<?php

/***************************************************************************
 *
 *   OUGC Thread Contributors plugin (/inc/languages/espanol/admin/ougc_threadcontributors.lang.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2014-2020 Omar Gonzalez
 *   
 *   Website: https://ougc.network
 *
 *   Shows a list of users who contributed to a thread discussion.
 *
 ***************************************************************************

****************************************************************************
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
****************************************************************************/

// Plugin API
$l['setting_group_ougc_threadcontributors'] = 'OUGC Thread Contributors';
$l['setting_group_ougc_threadcontributors_desc'] = 'Muestra un listado de usuarios que han colaborado en una discusión.';

// Settings
$l['setting_ougc_threadcontributors_showavatars'] = 'Mostrar Avatares';
$l['setting_ougc_threadcontributors_showavatars_desc'] = 'Si se desactiva esta opción se mostrara solo el nombre de usuario.';
$l['setting_ougc_threadcontributors_showavatars_guests'] = 'Mostrar Avatares a Invitados';
$l['setting_ougc_threadcontributors_showavatars_guests_desc'] = 'Si se desactiva esta opción se mostrara solo el nombre de usuario.';
$l['setting_ougc_threadcontributors_count_posts'] = 'Mostrar Total de Mensajes';
$l['setting_ougc_threadcontributors_count_posts_desc'] = 'Si se desactiva esta opción se mostrara el numero total de mensajes visibles por usuario.';
$l['setting_ougc_threadcontributors_orderby'] = 'Ordenar Por';
$l['setting_ougc_threadcontributors_orderby_desc'] = 'Por favor selecciona el campo por el cual ordenar a los usuarios.';
$l['setting_ougc_threadcontributors_orderby_username'] = 'Nombre de usuario';
$l['setting_ougc_threadcontributors_orderby_posttime'] = 'Fecha del mensaje';
$l['setting_ougc_threadcontributors_orderby_postcount'] = 'Total de mensajes';
$l['setting_ougc_threadcontributors_orderdir'] = 'Revertir el Orden';
$l['setting_ougc_threadcontributors_orderdir_desc'] = 'Selecciona si quieres revertir el orden de los usuarios.';
$l['setting_ougc_threadcontributors_ignoreauthor'] = 'Ignorar Autores';
$l['setting_ougc_threadcontributors_ignoreauthor_desc'] = 'Selecciona si el creador del tema debe ser ignorado en el listado de usuarios.';
$l['setting_ougc_threadcontributors_maxsize'] = 'Dimensiones Maximas';
$l['setting_ougc_threadcontributors_maxsize_desc'] = 'Por favor selecciona las dimensiones maximas tanto para el ancho como el alto de la imagen.';

// PluginLibrary
$l['ougc_threadcontributors_pl_required'] = 'Este complemento require la version {2} de <a href="{1}">PluginLibrary</a>. Por favor instala los archivos necesarios.';