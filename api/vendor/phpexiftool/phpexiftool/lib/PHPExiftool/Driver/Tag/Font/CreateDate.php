<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\Font;

use PHPExiftool\Driver\AbstractTag;

class CreateDate extends AbstractTag
{

    protected $Id = 'Creation Date';

    protected $Name = 'CreateDate';

    protected $FullName = 'Font::AFM';

    protected $GroupName = 'Font';

    protected $g0 = 'Font';

    protected $g1 = 'Font';

    protected $g2 = 'Document';

    protected $Type = '?';

    protected $Writable = false;

    protected $Description = 'Create Date';

    protected $local_g2 = 'Time';

}