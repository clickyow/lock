<?php

/***************************************************************************
 *
 *    Lock plugin (/inc/languages/espanol/admin/lock.lang.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Lock is a MyBB plugin for hiding content and selling it for your Newpoints currency.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

$l['lock'] = "Lock";
$l['lock_desc'] = "Lock es un complemento para ocultar contenido que se muestra cuando el usuario responde al hilo o paga puntos de Newpoints.";

$l['lock_pluginlibrary'] = "Este plugin requiere por lo menos la version {2} de <a href=\"{1}\">PluginLibrary</a>. Por favor sube a tu servidor los archivos necesarios para continuar.";

$l['setting_group_lock'] = "Lock";
$l['setting_group_lock_desc'] = "Configuracion para el plugin Lock.";
$l['setting_lock_key'] = "Key";
$l['setting_lock_key_desc'] = "Una palabra clave para mantener a curiosos de editar contenido que no deberian.";
$l['setting_lock_purchases_enabled'] = "Habilitar Compras Newpoints";
$l['setting_lock_purchases_enabled_desc'] = "Permite a los usuarios el vender contenido por puntos de Newpoints";
$l['setting_lock_allow_user_prices'] = "Permitir Precios Individuales";
$l['setting_lock_allow_user_prices_desc'] = "Permite a los usuarios el asignar el costo de su contenido de forma individual.";
$l['setting_lock_default_price'] = "Precio Predeterminado";
$l['setting_lock_default_price_desc'] = "El precio determinado para el contenido oculto. Deja como 0 para solicitar a los usuarios responder al tema para ver el contenido.";
$l['setting_lock_tax'] = "Impuesto";
$l['setting_lock_tax_desc'] = "Impuesto en porcentaje de los puntos que cada usuario gasta en contenido oculto (umaximo de 100%).";
$l['setting_lock_exempt'] = "Grupos Exentos";
$l['setting_lock_exempt_desc'] = "Selecciona a los grupos que estan exentos y pueden ver todo el contenido oculto.";
$l['setting_lock_disabled_forums'] = "Desactivar Compras Newpoints en Foros";
$l['setting_lock_disabled_forums_desc'] = "Selecciona los foros en los cuales no se puede cobrar por el contenido oculto.";
$l['setting_lock_type'] = "Usar '[hide]' en lugar de '[lock'].";
$l['setting_lock_type_desc'] = "Puedes utilizar cualquiera de las palabras clave.";

$l['lock_permission_maxcost'] = "Maximo precio para contenido oculto.";
$l['lock_permission_maxcost_desc'] = "Coloca la cantidad maxima que los usuarios de este grupo pueden cobrar por contenido oculto. Deja vacio para no tener limite. (Maximo: 99999)";