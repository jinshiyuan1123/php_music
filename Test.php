<?php 

    /**
     *发送数据处理
     * @param string $url 提交地址
     * @param string $senddata 提交参数
     * @param string $mykey  商户密钥
     * @param string $sysId  商户号
     */
    private  function send_data($url,$senddata,$mykey,$sysId){

            //3DES算法加密
            if(isset($senddata['idCardNo'])){
                $senddata['idCardNo']=$this->encode($senddata['idCardNo'],$mykey);
            }
            if(isset($senddata['bankAccountNo'])){
                $senddata['bankAccountNo']=$this->encode($senddata['bankAccountNo'],$mykey);
            }
            if(isset($senddata['mobile'])){
                $senddata['mobile']=$this->encode($senddata['mobile'],$mykey);
            }
            if(isset($senddata['cvn2'])){
                $senddata['cvn2']=$this->encode($senddata['cvn2'],$mykey);
            }
            if(isset($senddata['expired'])){
                $senddata['expired']=$this->encode($senddata['expired'],$mykey);
            }
            $sign=$this->make_sgin($senddata,$mykey);

            $headers=['sign:'.$sign,'sysId:'.$sysId,'Content-Type:application/json'];

            $ret=$this->curlpost($url,json_encode($senddata,JSON_UNESCAPED_UNICODE),'post',$headers);
            return $ret;
    }

    //计算签名
    private  function make_sgin($data,$mykey){
        ksort($data);
        $getdata = $data;//除去签名数据
        if (isset($getdata['sign'])) {
            unset($getdata['sign']);
        }
        $sginstr='';
        foreach ($getdata as $key =>$val){
            if(count($val) != count($val, 1))
            {
                foreach ($val as $k =>$v){
                    if(isset($v['channelCode'])){
                        $sginstr=$sginstr.'[{channelCode='.$v['channelCode'].', ';
                    }
                    if(isset($v['payRate'])){
                        $sginstr=$sginstr.'payRate='.$v['payRate'].'}]';
                    }
                }
            }else{
                $sginstr=$sginstr.$val;
            }
        }
        $sgin=hash('sha256',$sginstr.$mykey);
        return $sgin;
    }
    /**
     *post请求
     * @param string 
     * @param string 
     * @return string
     */
    private function curlpost($URL, $params, $type = "post", $headers = 0){
    
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $file_contents = curl_exec($ch);//获得返回值

        if (curl_error($ch)) {
            var_dump('错误：'.$URL.'=>('.$type.')'.curl_error($ch));exit;
        }
        return $file_contents;
    }
    /**
     *加密
     * @param string $string 需要加密的字符串
     * @param string $key 密钥
     * @return string
     */
    private function encode($string, $key)
    {
        // 对接java，服务商做的AES加密通过SHA1PRNG算法（只要password一样，每次生成的数组都是一样的），Java的加密源码翻译php如下：
        $key = substr(openssl_digest(openssl_digest($key, 'sha1', true), 'sha1', true), 0, 16);
        $data = openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = base64_encode($data); 
        return $data;
    }