<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\IPTC;

use PHPExiftool\Driver\AbstractTag;

class SubFile extends AbstractTag
{

    protected $Id = 10;

    protected $Name = 'SubFile';

    protected $FullName = 'IPTC::ObjectData';

    protected $GroupName = 'IPTC';

    protected $g0 = 'IPTC';

    protected $g1 = 'IPTC';

    protected $g2 = 'Other';

    protected $Type = '?';

    protected $Writable = false;

    protected $Description = 'Sub File';

    protected $flag_Binary = true;

    protected $flag_List = true;

}