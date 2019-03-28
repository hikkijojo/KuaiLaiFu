# KuaiLaiFu 大陸 快樂付第三方金流 SDK



## 无卡支付 使用範例
    $obj = new KuaiLaiFuPayGeteWay();
    print_r( $obj->getKuaiLaiFuPayResult( '20.00', 'pay2019032700001', 'testRMB20', '可為空' ) );



## 无卡支付查询 使用範例
  $obj = new KuaiLaiFuPayQuery();
  print_r( $obj->getKuaiLaiFuPayQuery( '2019032700001' ) );


## 代付
    $obj = new KuaiLaiFuProxyPay();
    $obj->bankAccType = 'PRIVATE_DEBIT_ACCOUNT';
    $obj->bankAccName = '許功蓋';
    $obj->bankAccNo = '1234567890123456';
    $obj->bankBranchNo = '123456789012';
    $obj->bankCode = 'SPDB';
    $obj->bankName = '上海浦东发展银行XXXXXX支行';
    $obj->province = 'XX省';
    $obj->city = 'XX市';
    $obj->amount = '1.00';
    $obj->proxyPayNo = 'proxypay201903280001';
    print_r( $obj->getKuaiLaiFuProxyPayResult() );


## 代付查询
    $obj = new KuaiLaiFuProxyQuery();
    print_r( $obj->getKuaiLaiFuProxyQuery( 'proxypay201903270001' ) );


## 余额查询
    $obj = new KuaiLaiFuQueryBalance();
    print_r( $obj->getKuaiLaiFuBalance() );
