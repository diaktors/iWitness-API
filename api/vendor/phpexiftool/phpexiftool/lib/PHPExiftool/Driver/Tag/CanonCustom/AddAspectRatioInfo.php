<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\CanonCustom;

use PHPExiftool\Driver\AbstractTag;

class AddAspectRatioInfo extends AbstractTag
{

    protected $Id = 2062;

    protected $Name = 'AddAspectRatioInfo';

    protected $FullName = 'CanonCustom::Functions2';

    protected $GroupName = 'CanonCustom';

    protected $g0 = 'MakerNotes';

    protected $g1 = 'CanonCustom';

    protected $g2 = 'Camera';

    protected $Type = 'int32s';

    protected $Writable = true;

    protected $Description = 'Add Aspect Ratio Info';

    protected $flag_Permanent = true;

    protected $Values = array(
        0 => array(
            'Id' => 0,
            'Label' => 'Off',
        ),
        1 => array(
            'Id' => 1,
            'Label' => '6:6',
        ),
        2 => array(
            'Id' => 2,
            'Label' => '3:4',
        ),
        3 => array(
            'Id' => 3,
            'Label' => '4:5',
        ),
        4 => array(
            'Id' => 4,
            'Label' => '6:7',
        ),
        5 => array(
            'Id' => 5,
            'Label' => '10:12',
        ),
        6 => array(
            'Id' => 6,
            'Label' => '5:7',
        ),
    );

}