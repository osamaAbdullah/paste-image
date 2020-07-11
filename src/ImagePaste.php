<?php

namespace Osama\ImagePaste;

use Laravel\Nova\Fields\Image;

class ImagePaste extends Image
{
   
    public $component = 'paste-image';
    
	public function url($url)
	{
		$this->preview(function () use ($url) {
			return $url;
		});
		return $this->withMeta(['url' => $url]);
	}
	
}
