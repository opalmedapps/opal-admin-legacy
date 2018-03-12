<?php
/**
 *
 * Encrypt class
 */
class Encrypt {

	/**
	 * Encodes or decodes string according to key
	 * 
	 * @access public
	 * @param mixed $str
	 * @param mixed $decodeKey
	 * @return string
	 */
	 
	public function encodeString($str,$decodeKey) {
       $result = "";
       $str = base64_decode($str); // encoded on frontend
       for($i = 0;$i < strlen($str);$i++) {
        	$a = $this->_getCharcode($str,$i);
        	$b = $a ^ $decodeKey;
        	$result .= $this->_fromCharCode($b);
       }
    
       return $result;
    }

    /**
     * PHP replacement for JavaScript charCodeAt.
     * 
     * @access private
     * @param mixed $str
     * @param mixed $i
     * @return string
     */
    private function _getCharcode($str,$i) {
         return $this->_uniord(substr($str, $i, 1));
    }

    /**
     * Gets character from code.
     * 
     * @access private
     * @return string
     */
    private function _fromCharCode(){
      $output = '';
      $chars = func_get_args();
      foreach($chars as $char){
        $output .= chr((int) $char);
      }
      return $output;
    }


    /**
     * Multi byte ord function.
     * 
     * @access private
     * @param mixed $c
     * @return mixed
     */
    private function _uniord($c) {
        $h = ord($c{0});
        if ($h <= 0x7F) {
            return $h;
        } else if ($h < 0xC2) {
            return false;
        } else if ($h <= 0xDF) {
            return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
        } else if ($h <= 0xEF) {
            return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6 | (ord($c{2}) & 0x3F);
        } else if ($h <= 0xF4) {
            return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12 | (ord($c{2}) & 0x3F) << 6 | (ord($c{3}) & 0x3F);
        } else {
            return false;
        }
    }
}

?>