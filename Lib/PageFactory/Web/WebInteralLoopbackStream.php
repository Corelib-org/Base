<?php
class WebInteralLoopbackStream extends LoopbackStream {
	public function stream_open($path , $mode , $options , &$opened_path){
		$url = parse_url($path);
		$path = str_replace($url['scheme'].'://', BASE_URL, $path);
		return parent::stream_open($path , $mode , $options , &$opened_path);
	}
}
stream_wrapper_register('internal', 'WebInteralLoopbackStream');
?>