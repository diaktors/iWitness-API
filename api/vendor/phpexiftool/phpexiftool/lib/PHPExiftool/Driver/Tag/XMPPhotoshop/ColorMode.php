<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\XMPPhotoshop;

use PHPExiftool\Driver\AbstractTag;

class ColorMode extends AbstractTag
{

    protected $Id = 'ColorMode';

    protected $Name = 'ColorMode';

    protected $FullName = 'XMP::photoshop';

    protected $GroupName = 'XMP-photoshop';

    protected $g0 = 'XMP';

    protected $g1 = 'XMP-photoshop';

    protected $g2 = 'Image';

    protected $Type = 'integer';

    protected $Writable = true;

    protected $Description = 'Color Mode';

    protected $Values = array(
        0 => array(
            'Id' => 0,
            'Label' => 'Bitmap',
        ),
        1 => array(
            'Id' => 1,
            'Label' => 'Grayscale',
        ),
        2 => array(
            'Id' => 2,
            'Label' => 'Indexed',
        ),
        3 => array(
            'Id' => 3,
            'Label' => 'RGB',
        ),
        4 => array(
            'Id' => 4,
            'Label' => 'CMYK',
        ),
        7 => array(
            'Id' => 7,
            'Label' => 'Multichannel',
        ),
        8 => array(
            'Id' => 8,
            'Label' => 'Duotone',
        ),
        9 => array(
            'Id' => 9,
            'Label' => 'Lab',
        ),
    );

}
