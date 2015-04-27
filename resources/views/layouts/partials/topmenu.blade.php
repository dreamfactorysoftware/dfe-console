<?php
use DreamFactory\Library\Utility\IfSet;
use DreamFactory\Library\Utility\Inflector;

$menu = array();

array_push($menu, array('id' => 0,  'link' => 'dashboard',     'name' => 'Dashboard'));
array_push($menu, array('id' => 1,  'link' => 'users',          'name' => 'Users'));
array_push($menu, array('id' => 2,  'link' => 'servers',        'name' => 'Servers'));
array_push($menu, array('id' => 3,  'link' => 'clusters',       'name' => 'Clusters'));
array_push($menu, array('id' => 4,  'link' => 'instances',      'name' => 'Instances'));
array_push($menu, array('id' => 5,  'link' => 'policies',       'name' => 'Policies'));
array_push($menu, array('id' => 5,  'link' => 'reports',        'name' => 'Reports'));

?>

<div style="margin: 10px auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div style="margin: 10px auto">
                    <h1 style="margin-left: 0px;" class="df-component-nav-title pull-left">
                        {{$pageName}}
                    </h1>

                    <df-component-nav >
                        <div class="df-component-navbar">
                            <ul class="nav nav-pills pull-right visible-md visible-lg">
                                @foreach ($menu as $menu_item)

                                    @if ($menu_item['name'] == $pageName)
                                        <li class="active">
                                    @else
                                        <li class="">
                                    @endif
                                            @if ($menu_item['name'] == 'Dashboard')
                                                <a href="/">{{ $menu_item['name'] }}</a>
                                            @else
                                                <a href="/{{$prefix}}/{{ $menu_item['link'] }}">{{ $menu_item['name'] }}</a>
                                            @endif
                                        </li>
                                @endforeach
                            </ul>
                        </div>
                    </df-component-nav>
                </div>
            </div>
        </div>
    </div>
</div>