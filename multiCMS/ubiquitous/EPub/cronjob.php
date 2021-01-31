<?php
/*
 *  This file is part of lightCRM.

 *  lightCRM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  lightCRM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
date_default_timezone_set('Europe/Berlin');

session_name("ExtConnEPub");

if(isset($argv[1]))
	$_SERVER["HTTP_HOST"] = $argv[1];

#require_once realpath(dirname(__FILE__)."/../../system/connect.php");
require_once dirname(__FILE__)."/../../classes/toolbox/Util.class.php";
require_once dirname(__FILE__)."/../../classes/frontend/ExtConn.class.php";
require_once dirname(__FILE__)."/../../plugins/Customizer/iCustomizer.class.php";
require_once dirname(__FILE__)."/../../classes/interfaces/iFileBrowser.class.php";
require_once dirname(__FILE__)."/../../specifics/CustomizerFurtmeierSuchlotsen.class.php";


$e = new ExtConn(Util::getRootPath());

$e->useDefaultMySQLData();
$e->useUser();

require_once dirname(__FILE__)."/../../libraries/HTMLTidy.class.php";
require_once dirname(__FILE__)."/../../libraries/HTMLSlicer.class.php";
require_once dirname(__FILE__)."/iEPubParser.class.php";
require_once dirname(__FILE__)."/mEPubDL.class.php";
require_once dirname(__FILE__)."/EPubDL.class.php";
require_once dirname(__FILE__)."/gbSpiegelParserGUI.class.php";
require_once dirname(__FILE__)."/EPub.class.php";

mEPubDL::DL();

$e->cleanUp();
?>