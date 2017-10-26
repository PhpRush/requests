<?php
namespace PhpRush\Requests;

/**
 *
 * @author jiangjianyong
 *        
 */
interface Transport
{

    public function request($url, $headers = array(), $data = array(), $options = array());
}

?>