<?php

namespace Perpii\FFMpeg\Format\Video;


class X264 extends  \FFMpeg\Format\Video\X264 {

    /**
     * {@inheritDoc}
     */
    public function getAvailableAudioCodecs()
    {
        return array('aac', 'libfaac', 'libfdk_aac', 'libvo_aacenc');
    }
} 