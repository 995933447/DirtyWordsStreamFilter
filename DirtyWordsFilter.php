<?php 
class DirtyWordsFilter extends php_user_filter
{
	private $dirty = ['grime', 'dirty', 'grease'];

	/**
	 * [filter description]
	 * @param  [type] $in        [流来的桶队列]
	 * @param  [type] $out       [流走的桶队列]
	 * @param  [type] &$consumed [处理的字节数]
	 * @param  [type] $closing   [是否处理最后一个桶队列]
	 * @return [type]            [description]
	 */
	public function filter($in, $out, &$consumed, $closing)
	{
		$wordData = [];
		foreach ($this->dirty as $key => $value) {
			$wordData[] = implode('', array_fill(0, mb_strlen($value), '*'));
		}

		//桶的数据量并非根据流函数决定，每个桶的数据量都是固定的，如这里是有fgets每次获取一行，实际这里一个桶一次性包含了所有数据
		while($bucket = stream_bucket_make_writeable($in)) {
			//桶队列每个对象有2个属性，分别是data和datalen
			$bucket->data = str_replace($this->dirty, $wordData, $bucket->data);
			$consumed += $bucket->datalen;
			stream_bucket_append($out, $bucket);
		}

		return PSFS_PASS_ON;
	}
}

stream_filter_register('dirty_words_filter', DirtyWordsFilter::class);

$handle = fopen('data.txt', 'r+');
stream_filter_append($handle, 'dirty_words_filter');
while (!feof($handle)) {
	echo fgets($handle);
}

fclose($handle);