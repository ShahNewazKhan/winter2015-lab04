<?php

/**
 * Order handler
 * 
 * Implement the different order handling usecases.
 * 
 * controllers/welcome.php
 *
 * ------------------------------------------------------------------------
 */
class Order extends Application {

    function __construct() {
        parent::__construct();
    }

    // start a new order
    function neworder() 
    {
        //FIXME
        $order_num = $this->orders->highest() + 1;
        $order = $this->orders->create();

        $order->num = $order_num;
        $order->date = date("Y/m/d/H/i/s");
        $order->status = 'a';

        $this->orders->add($order);
        redirect('/order/display_menu/' . $order_num);
    }

    // add to an order
    function display_menu($order_num = null) 
    {
        if ($order_num == null)
            redirect('/order/neworder');

        $this->data['pagebody'] = 'show_menu';
        $this->data['order_num'] = $order_num;

        //FIXME
        $order = $this->orders->get($order_num);
        $total = $this->orders->total($order_num);

        setlocale(LC_MONETARY, 'en_CA');
        
        $total = money_format('%i', $total); 

        $this->data['title'] = "Order # " . $order->num . " Total: " . $total ;

        // Make the columns
        $this->data['meals'] = $this->make_column('m', $order_num);
        $this->data['drinks'] = $this->make_column('d', $order_num);
        $this->data['sweets'] = $this->make_column('s', $order_num);

        $this->render();
    }

    // make a menu ordering column
    function make_column($category, $num) {
        //FIXME
        //return $items;
        $results = $this->menu->some('category',$category);

        foreach( $results as $result)
        {
            $result->order_num = $num;   
        }

        return $results;
    }

    // add an item to an order
    function add($order_num, $item) {
        //FIXME
        $this->orders->add_item($order_num, $item);
        redirect('/order/display_menu/' . $order_num);
    }

    // checkout
    function checkout($order_num) {
        $this->data['title'] = 'Checking Out';
        $this->data['pagebody'] = 'show_order';
        $this->data['order_num'] = $order_num;
        $this->data['total'] = number_format( $this->orders->total($order_num) , 2);
        //FIXME
        $items = $this->orderitems->group($order_num);

        foreach($items as $item)
        {
            $menuitem = $this->menu->get($item->item);
            $item->code = $menuitem ->name;
        }
        $this->data['okornot'] = $this->orders->validate($order_num);
        $this->data['items'] = $items;
        $this->render();
    }

    // proceed with checkout
    function commit($order_num) 
    {
        //FIXME
        if(!$this->orders->validate($order_num))
            redirect('/order/display_menu/'. $order_num);
        $record = $this->orders->get($order_num);
        $record->date = date(DATE_ATOM);
        $record->status = 'c';
        $record->total = $this->orders->total($order_num);
        $this->orders->update($record);
        redirect('/');
    }

    // cancel the order
    function cancel($order_num) 
    {
        //FIXME
        $this->orderitems->delete_some($order_num);
        $record = $this->order->get($order_num);
        $record->status = 'x';
        $this->orders->update($record);
        redirect('/');
    }

}
