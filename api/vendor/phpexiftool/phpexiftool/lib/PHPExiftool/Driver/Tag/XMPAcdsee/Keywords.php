<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\XMPAcdsee;

use PHPExiftool\Driver\AbstractTag;

class Keywords extends AbstractTag
{

    protected $Id = 'keywords';

    protected $Name = 'Keywords';

    protected $FullName = 'XMP::acdsee';

    protected $GroupName = 'XMP-acdsee';

    protected $g0 = 'XMP';

    protected $g1 = 'XMP-acdsee';

    protected $g2 = 'Image';

    protected $Type = 'string';

    protected $Writable = true;

    protected $Description = 'Keywords';

    protected $flag_Avoid = true;

    protected $flag_List = true;

    protected $flag_Bag = true;

}