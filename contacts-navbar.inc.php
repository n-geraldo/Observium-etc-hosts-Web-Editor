<?php

  $navbar['obsc'] = array('url' => '#', 'icon' => $config['icon']['settings'], 'title' => 'Custom Tools');
  $separator = 0;
  
  $navbar['obsc']['entries'][] = array('url' => generate_url(array('page' => 'customers')), 'icon' => $config['icon']['port-customer'], 'title' => 'Databaza e Klienteve Biznes');
  
  
  $navbar['obsc']['entries'][] = array('url' => generate_url(array('page' => 'hosts_edit')), 'icon' => $config['icon']['diskio'], 'title' => 'HOSTS');
?>
