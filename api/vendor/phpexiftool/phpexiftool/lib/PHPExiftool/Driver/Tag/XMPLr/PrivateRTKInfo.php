<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\XMPLr;

use PHPExiftool\Driver\AbstractTag;

class PrivateRTKInfo extends AbstractTag
{

    protected $Id = 'privateRTKInfo';

    protected $Name = 'PrivateRTKInfo';

    protected $FullName = 'XMP::Lightroom';

    protected $GroupName = 'XMP-lr';

    protected $g0 = 'XMP';

    protected $g1 = 'XMP-lr';

    protected $g2 = 'Image';

    protected $Type = 'string';

    protected $Writable = true;

    protected $Description = 'Private RTK Info';

}