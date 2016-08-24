<?php

/*
    HCSF - A multilingual CMS and Shopsystem
    Copyright (C) 2014  Marcus Haase - mail@marcus.haase.name

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require __DIR__.'/../app/init.php';

$response = new Zend\Diactoros\Response();
$response = $response->withStatus($P->status)->withHeader('Content-Language', $container['lang']);
$omitbody = false;

if (!empty($P->headers)) {
    foreach ($P->headers as $key => $value) {
        $response = $response->withHeader($key, $value);
        if (mb_strtolower($key) == 'location') {
            $omitbody = true;
        }
    }
}

$P = HaaseIT\HCSFNG\Frontend\Helper::enrichPagePayload($container, $P);

if (!$omitbody){
    $response->getBody()->write($container['twig']->render($container['conf']["template_base"], $P->payload));
}

$emitter = new Zend\Diactoros\Response\SapiEmitter();
$emitter->emit($response);
