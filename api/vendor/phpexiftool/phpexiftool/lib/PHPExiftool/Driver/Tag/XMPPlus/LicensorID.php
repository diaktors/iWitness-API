<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\XMPPlus;

use PHPExiftool\Driver\AbstractTag;

class LicensorID extends AbstractTag
{

    protected $Id = 'LicensorLicensorID';

    protected $Name = 'LicensorID';

    protected $FullName = 'XMP::plus';

    protected $GroupName = 'XMP-plus';

    protected $g0 = 'XMP';

    protected $g1 = 'XMP-plus';

    protected $g2 = 'Author';

    protected $Type = 'string';

    protected $Writable = true;

    protected $Description = 'Licensor ID';

    protected $flag_List = true;

}