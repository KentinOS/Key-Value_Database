<?php
/**
 *Meta data is encrpted && key-value is directly saved. 
 */

#Key-Value Database
include_once 'config.php';
#The Local Config
define('DEFAULT_MODE',  DB_PUBLIC_MODE);
define('OK',            DB_SUCCESS);
define('ERROR',         DB_FAILED);

define('KEY_SIZE',      DB_KEY_SIZE);
define('KEY_EXISTS',    DB_KEY_EXISTS);
define('KEY_NOT_EXISTS',DB_KEY_NOT_EXISTS);
define('KEY_INVALID',   DB_KEY_INVALID);

define('IDX_SUFFIX',    DB_INDEX_SUFFIX);
define('DAT_SUFFIX',    DB_DATA_SUFFIX);
define('BUCKET_SIZE',   DB_BUCKET_SIZE);
define('INDEX_SIZE',    DB_INDEX_SIZE);


#The KVDB
    class Database{
        private $idx_fp=NULL;
        private $dat_fp=NULL;
        private $closed=FALSE;

        public function __construct(){

        }

        public function open($pathname,$access_mode=DEFAULT_MODE,$oauth_mode=DEFAULT_MODE){
            $idx_file=$pathname.IDX_SUFFIX;
            $dat_file=$pathname.DAT_SUFFIX;
            if(file_exists($idx_file)){
                $init=false;
                $mode='r+b';
            }else{
                $init=true;
                $mode='w+b';
            }
            if($access_mode != DEFAULT_MODE){
                $mode=$access_mode;
            }
            $this->idx_fp=fopen($idx_file,$mode);
            if(!$this->idx_fp){
                return ERROR;
            }
            if($init){
                $init_data=pack('L',0x00000000);
                for($i=0;$i<BUCKET_SIZE;$i++){
                    fwrite($this->idx_fp,$init_data,4);
                }
            }
            $this->dat_fp=fopen($dat_file,$mode);
            if(!$this->dat_fp){
                return ERROR;
            }
            return OK;
        }

        public function close(){
            if(!$this->closed){
                fclose($this->idx_fp);
                fclose($this->dat_fp);
                $this->closed=FALSE;
            }
            return OK;
        }



        public function insert($key,$data,$option=DB_STORE){
            $keylen=strlen($key);
            if($keylen>KEY_SIZE){
                return KEY_INVALID;
            }
            $res = $this->_hash_search($key);
            (var_dump($res));
            if(($res['code'] == OK) && ($option!=DB_REPLACE)){
                return KEY_EXISTS;
            }

            $idxoff = fstat($this->idx_fp);
            $idxoff = intval($idxoff['size']);

            $datoff = fstat($this->dat_fp);
            $datoff = intval($datoff['size']);

            $block = pack('L',0x00000000);
            $block .= $key;
            $space = KEY_SIZE-$keylen;
            for($i = 0; $i <$space; $i ++){
                $block .= pack('C',0x00);
            }
            $block .= pack('L',$datoff);
            $block .= pack('L',strlen($data));

            #die(var_dump($block));
            fseek($this->idx_fp,$res['idxoff'],SEEK_SET);
            fwrite($this->idx_fp,pack('L',$idxoff),4);

            fseek($this->idx_fp,0,SEEK_END);
            fwrite($this->idx_fp,$block,INDEX_SIZE);

            fseek($this->dat_fp,$datoff,SEEK_SET);
            fwrite($this->dat_fp,$data,strlen($data));

            return OK;
        }

        public function delete($key){
            $keylen = strlen($key);
            if($keylen>KEY_SIZE){
                return KEY_INVALID;
            }

            $offset = $this->_hash($key) %BUCKET_SIZE *4;
            
            fseek($this->idx_fp,$offset,SEEK_SET);
            $head = unpack('L',fread($this->idx_fp,4));
            $head = $head[1];

            $current = $head;
            $prev = 0x00000000;
            $next = 0x00000000;
            $found = false;

            while($current){
                fseek($this->idx_fp,$current,SEEK_SET);
                $tmp_block = fread($this->idx_fp,INDEX_SIZE);
                $cpkey = substr($tmp_block,4,$keylen);
                if(!strncmp($cpkey,$key,$keylen)){
                    $found = true;
                    break;
                }
                $next = unpack('L',substr($tmp_block,0,4));
                $next = $next[1];
                $prev = $current;
                $current = $next;
            }
            if(!$found){
                return KEY_NOT_EXISTS;
            }
            
            if($prev != 0){
                $offset = $prev;
            }
            fseek($this->idx_fp,$offset,SEEK_SET);
            fwrite($this->idx_fp,pack('L',$next));
            return OK;
        }

        public function fetch($key){
            $res = $this->_hash_search($key);
            #die(var_dump($res));
            if($res['code'] != OK){
                return NULL;
            }
            return fread($this->dat_fp,$res['datalen']);
        }
        private function _hash_search($key){
            $offset = $this->_hash($key) % BUCKET_SIZE *4;
            fseek($this->idx_fp,$offset,SEEK_SET);
            $pos = unpack('L',fread($this->idx_fp,4));
            $pos = $pos[1];
            $found = false;
            $keylen=strlen($key);
            $datoff = 0;
            $datalen = 0 ;
            while($pos){
                fseek($this->idx_fp,$pos,SEEK_SET);
                $tmp_block = fread($this->idx_fp,INDEX_SIZE);
                $cpkey = substr($tmp_block,4,$keylen);
                var_dump(INDEX_SIZE);
                if(!strncmp($cpkey,$key,$keylen)){
                    $datoff = unpack('L',substr($tmp_block,KEY_SIZE+4,4));
                    $datoff = $datoff[1];
                    $datalen = unpack('L',substr($tmp_block,KEY_SIZE+8,4));
                    $datalen = $datalen[1];
                    fseek($this->dat_fp,$datoff,SEEK_SET);
                    $found = true;
                    break; 
                }
                $offset = $pos;
                $pos = unpack('L',substr($tmp_block,0,4));
                $pos = $pos[1];
            }
            $res = array('idxoff'=>0,'datoff'=>0,'code'=>0,'datalen'=>$datalen);
            $res['idxoff'] = $offset;
            $res['datoff'] = $datoff;
            if($found){
                $res['code'] = OK;
            }else{
                $res['code'] = KEY_NOT_EXISTS;
            }
            return $res;
        }

        private function _hash($string){
            //times33算法
            $string = substr(md5($string),0,8);
            $hash = 0;
            for($i=0;$i<8;$i++){
                $hash += $hash*33+ord($string{$i});
            }
            return $hash & 0x7fffffff;
        }

        public function __destruct(){

        }
    }
