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

class FocusingScreen extends AbstractTag
{

    protected $Id = 'mixed';

    protected $Name = 'FocusingScreen';

    protected $FullName = 'mixed';

    protected $GroupName = 'CanonCustom';

    protected $g0 = 'MakerNotes';

    protected $g1 = 'CanonCustom';

    protected $g2 = 'Camera';

    protected $Type = 'mixed';

    protected $Writable = true;

    protected $Description = 'Focusing Screen';

    protected $flag_Permanent = true;

    protected $Values = array(
        0 => array(
            'Id' => 0,
            'Label' => 'Ec-N, R',
        ),
        1 => array(
            'Id' => 1,
            'Label' => 'Ec-A,B,C,CII,CIII,D,H,I,L',
        ),
        2 => array(
            'Id' => 0,
            'Label' => 'Ef-A',
        ),
        3 => array(
            'Id' => 1,
            'Label' => 'Ef-D',
        ),
        4 => array(
            'Id' => 2,
            'Label' => 'Ef-S',
        ),
        5 => array(
            'Id' => 0,
            'Label' => 'Eg-A',
        ),
        6 => array(
            'Id' => 1,
            'Label' => 'Eg-D',
        ),
        7 => array(
            'Id' => 2,
            'Label' => 'Eg-S',
        ),
        8 => array(
            'Id' => 0,
            'Label' => 'Ec-CIV',
        ),
        9 => array(
            'Id' => 1,
            'Label' => 'Ec-A,B,C,CII,CIII,D,H,I,L',
        ),
        10 => array(
            'Id' => 2,
            'Label' => 'Ec-S',
        ),
        11 => array(
            'Id' => 3,
            'Label' => 'Ec-N,R',
        ),
        12 => array(
            'Id' => 0,
            'Label' => 'Ee-A',
        ),
        13 => array(
            'Id' => 1,
            'Label' => 'Ee-D',
        ),
        14 => array(
            'Id' => 2,
            'Label' => 'Ee-S',
        ),
    );

    protected $Index = 'mixed';

}
