<?php

exit('Disabled');

ini_set('max_execution_time', 50000000);
//require_once "headconnection.php";
require_once "aws/aws-autoloader.php";
use Aws\Credentials\CredentialProvider;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
$profile = '';
$path =  __DIR__ .'/aws/AWSCredentials.ini';
// echo $path,$_SERVER['DOCUMENT_ROOT']; //exit;
$provider = CredentialProvider::ini($profile, $path);
// Cache the results in a memoize function to avoid loading and parsing
// the ini file on every API operation.
$provider = CredentialProvider::memoize($provider);
$bucketName = '';
try{
$client = new S3Client([
   'region'      => 'ap-south-1',
   'version'     => 'latest',
   'credentials' => $provider
]);
}
catch (AwsException $e) {  var_dump($e->getMessage()); }
try {
    $result = $client->putBucketCors([
        'Bucket' => $bucketName, // REQUIRED
        'CORSConfiguration' => [ // REQUIRED
            'CORSRules' => [ // REQUIRED
                [
                    'AllowedHeaders' => ['Authorization'],
                    'AllowedMethods' => ['POST', 'GET', 'PUT'], // REQUIRED
                    'AllowedOrigins' => ['*'], // REQUIRED
                    'ExposeHeaders' => [],
                    'MaxAgeSeconds' => 3000
                ],
            ],
        ]
    ]);
    //var_dump($result);
} catch (AwsException $e) {
    // output error message if fails
    error_log($e->getMessage());
}
 //try { $result = $client->listBuckets(); var_dump($result); } catch (AwsException $e) {  var_dump($e->getMessage()); }//exit();
$iterator = $client->getIterator('ListObjects', array(
   'Bucket' => $bucketName,
   'Prefix' =>'uploads/'
));
if(!empty($image_ins['image_url'])){
   $tempFile =''.$image_ins['image_url'];
   $fname = $image_ins['image_url'];
   $r = $client->putObject(array(
     'Bucket'     => $bucketName,
     'Key'        => $fname,
     'SourceFile' => $tempFile,
     'ContentType' =>'image/jpg',
     'ACL'          => 'public-read',
     'CacheControl' => 'max-age = 31536000',
   ));
}
if(!empty($image_ins['thumb_url'])){
  $tempFile =''.$image_ins['thumb_url'];
  $fname = $image_ins['thumb_url'];
  $s = $client->putObject(array(
     'Bucket'     => $bucketName,
     'Key'        => $fname,
     'SourceFile' => $tempFile,
     'ContentType' =>'image/jpg',
     'ACL'          => 'public-read',
     'CacheControl' => 'max-age = 31536000',
   ));
}
if(!empty($image_ins['list_url'])){
  $tempFile =''.$image_ins['list_url'];
  $fname = $image_ins['list_url'];
  $s = $client->putObject(array(
     'Bucket'     => $bucketName,
     'Key'        => $fname,
     'SourceFile' => $tempFile,
     'ContentType' =>'image/jpg',
     'ACL'          => 'public-read',
     'CacheControl' => 'max-age = 31536000',
   ));
}
if(!empty($image_ins['mobile_url'])){
  $tempFile =''.$image_ins['mobile_url'];
  $fname = $image_ins['mobile_url'];
  $s = $client->putObject(array(
     'Bucket'     => $bucketName,
     'Key'        => $fname,
     'SourceFile' => $tempFile,
     'ContentType' =>'image/jpg',
     'ACL'          => 'public-read',
     'CacheControl' => 'max-age = 31536000',
   ));
}
if(!empty($image_data['image_url'])){
  $tempFile =''.$image_data['image_url']; 
  $fname = $image_data['image_url'];
  $r = $client->putObject(array(
     'Bucket'     => $bucketName,
     'Key'        => $fname,
     'SourceFile' => $tempFile,
     'ContentType' =>'image/jpg',
     'ACL'          => 'public-read',
     'CacheControl' => 'max-age = 31536000',
   ));
}
if(!empty($img_path)){
  $tempFile ='uploads/default/files/'.$img_path; 
  $fname = 'uploads/images/'.$img_path;
  $r = $client->putObject(array(
     'Bucket'     => $bucketName,
     'Key'        => $fname,
     'SourceFile' => $tempFile,
     'ContentType' =>'image/jpg',
     'ACL'          => 'public-read',
     'CacheControl' => 'max-age = 31536000',
   ));
}?>