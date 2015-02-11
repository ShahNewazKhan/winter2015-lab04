<?php

/**
 * Data access wrapper for "orders" table.
 *
 * @author jim
 */
class Orders extends MY_Model {

    // constructor
    function __construct() {
        parent::__construct('orders', 'num');

        $CI = &get_instance(); 
        $CI->load->model('orderitems');
    }

    // add an item to an order
    function add_item($num, $code) 
    {
        //If item already exists, increment the qunatity
        if($this->orderitems->exists($num, $code))
        {
            $record = $this->orderitems->get($num, $code);
            $record->quantity +=  1;

            $this->orderitems->update($record);
        }
        else 
        {
            $newItem = $this->orderitems->create();
            $newItem->order = $num;
            $newItem->item = $code;
            $newItem->quantity = 1;
            $this->orderitems->add($newItem); 
        }    
    }

    // calculate the total for an order
    function total($num) 
    {
        $orderItems = $this->orderitems->group($num);

        $menuItems = $this->menu->all();

        $total = 0;

        foreach ($orderItems as $item ) 
        {
            foreach($menuItems as $menuItem)
            {
                if ($item->item == $menuItem->code)
                {
                    $total += $menuItem->price * $item->quantity;
                }
            }
        }

        return $total;
    }

    // retrieve the details for an order
    function details($num) 
    {
        
    }

    // cancel an order
    function flush($num) {
        
    }

    // validate an order
    // it must have at least one item from each category
    function validate($num) {
        
        $CI = & get_instance($num);
        $items = $CI->orderitems->group($num);
        $gotem = array();
        if(count($items) > 0)
            foreach($items as $item)
            {
                $menu = $CI->menu->get($item->item);
                $gotem[$menu->category] = 1;
            }
        return isset($gotem['m']) && isset($gotem['d']) && isset($gotem['s']);
    }

}
