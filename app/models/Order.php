<?php

class Order extends Eloquent
{
	public $guarded = [];

	public function dates()
	{
		return array('created_at', 'updated_at', 'shipped_at');
	}

	public function address()
	{
		return $this->belongsTo('Address');
	}

	public function getItemsAttribute()
	{
		$id = $this->getKey();

		$builder = $this->newBaseQueryBuilder();

		$items = $builder->from('item_order')
			->where('order_id', $id)
			->get();

		$collection = $this->newCollection();
		$instances = array();

		foreach($items as $item)
		{
			$type = $item->item_type;
			if (!isset($instances[$type])) {
				$instances[$type] = new $type;
			}

			$amount = $item->amount;
			$item = $instances[$type]->find($item->item_id);
			$item->amount = $amount;

			$collection->add($item);
		}

		return $collection;
	}

	public function getTotalAttribute()
	{
		$items = $this->getItemsAttribute();

		$total = 0;

		foreach ($items as $item) {
			$total += ($item->price * $item->amount);
		}

		return $total;
	}

	public function getFullNameAttribute()
	{
		return $this['attributes']['first_name'] . ' ' . $this['attributes']['last_name'];
	}

	public function getShippedAtAttribute()
	{
		return new Carbon\Carbon($this['attributes']['shipped_at']);
	}
}