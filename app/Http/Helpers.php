<?php

class Helpers {
    /*
     * Method to strip tags globally.
     */

    public static function globalXssClean() {
        // Recursive cleaning for array [] inputs, not just strings.
        $sanitized = static::arrayStripTags(Request::all());
        Request::merge($sanitized);
    }

    /**
     * Method to strip tags
     *
     * @param $array
     * @return array
     */
    public static function arrayStripTags($array) {
        $result = array();

        foreach ($array as $key => $value) {
            // Don't allow tags on key either, maybe useful for dynamic forms.
            $key = strip_tags($key);

            // If the value is an array, we will just recurse back into the
            // function to keep stripping the tags out of the array,
            // otherwise we will set the stripped value.
            if (is_array($value)) {
                $result[$key] = static::arrayStripTags($value);
            } else {
                // I am using strip_tags(), you may use htmlentities(),
                // also I am doing trim() here, you may remove it, if you wish.
                $result[$key] = trim(strip_tags($value));
            }
        }

        return $result;
    }

    /**
     * Escape output
     *
     * @param $value
     * @return string
     */
    public static function sanitizeOutput($value) {
        return addslashes($value);
    }

    /*
     * Convert date
     */

    public static function convertDate($convertDate) {
        if ($convertDate != '') {
            $convertDate = str_replace('/', '-', $convertDate);
            return date('Y-m-d', strtotime($convertDate));
        }
    }

    /**
     * Send success ajax response
     *
     * @param string $message
     * @param array $result
     * @return array
     */
    public static function sendSuccessAjaxResponse($message = '', $result = []) {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result
        ];

        return $response;
    }

    /**
     * Send failure ajax response
     *
     * @param string $message
     * @return array
     */
    public static function sendFailureAjaxResponse($message = '') {
        $message = $message == '' ? config('app.message.default_error') : $message;

        $response = [
            'status' => false,
            'message' => $message,
            'data' => []
        ];

        return $response;
    }

    /**
     * function for send email
     */
    public static function sendEmail($template, $data, $toEmail, $toName, $subject, $fromName = '', $fromEmail = '',$attachment = '') {

        \Mail::send($template, $data, function ($message) use($toEmail, $toName, $subject, $data, $fromName, $fromEmail, $attachment) {
            $message->to($toEmail, $toName);
            $message->subject($subject);

            if ($fromEmail != '' && $fromName != '') {
                $message->from($fromEmail, $fromName);
            }

            if($attachment != ''){
                $message->attach($attachment);
            }
        });
    }

    /**
     * Generate password
     * @param int $length
     * @return string
     */
    public static function generatePassword($length = 12) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    /**
     * Resize image
     * @param $fileToResize
     * @return mixed
     */
    public static function resizeImage($imageToResize) {
        $img = Image::make($imageToResize)->resize(1200, null)->encode('jpg', 80)->save();
        return $img->basename;
    }

    /**
     * Convert image to jpg
     * @param $imageToConvert
     * @param $convertedFile
     * @return string
     */
    public static function convertToJpg($imageToConvert, $convertedFile) {
        $img = Image::make($imageToConvert)->encode('jpg', 80)->save($convertedFile);
        unlink($imageToConvert);
        return $img->dirname . '/' . $img->basename;
    }

    /**
     * Get image width
     * @param $image
     * @return mixed
     */
    public static function getImageWidth($image) {
        return Image::make($image)->width();
    }

    /**
     * Get image height
     * @param $image
     * @return mixed
     */
    public static function getImageHeight($image) {
        return Image::make($image)->height();
    }

    /**
     * Create folder
     * @param $path
     * @return bool
     */
    public static function createFolder($path) {
        return \File::makeDirectory($path, 0777);
    }

    /**
     * Upload files other than images
     * @param $document
     * @param $dir
     * @return string
     */
    public static function uploadDocuments($document, $dir, $fileName = '') {
        $date = new DateTime();
        $currentTimeStamp = $date->getTimestamp();

        if ($fileName == '') {
            $documentOriginalName = $currentTimeStamp . '_' . $document->getFilename() . '.' . $document->getClientOriginalExtension();
        } else {
            $documentOriginalName = $fileName . '.' . $document->getClientOriginalExtension();

            //Remove file first if exist
            if (file_exists($dir . $documentOriginalName)) {
                unlink($dir . $documentOriginalName);
            }
        }

        //Store file to folder
        $document->move($dir, $documentOriginalName);
        return $documentOriginalName;
    }

    /**
     * Upload file
     * @param $file
     * @param $dir
     * @return mixed|string
     */
    public static function uploadFiles($file, $dir, $convert = true) {
        $date = new DateTime();
        $currentTimeStamp = $date->getTimestamp();
        $fileOriginalName = $currentTimeStamp . '_' . $file->getFilename() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $fileOriginalName);
        $finalDoc = $fileOriginalName;

        //Get file mime type
        $mimeType = $file->getClientMimeType();
        //Optimize if uploaded file is image
        if (self::checkImageMimes($mimeType)) {
            $imageToResize = $dir . $fileOriginalName;
            $imageName = pathinfo($fileOriginalName, PATHINFO_FILENAME);
            $finalDoc = $fileOriginalName;

            //Convert png to jpg
            if ($mimeType == 'image/png' && $convert == true) {
                $convertedFile = $imageName . '.jpg';
                $convertedFilePath = $dir . $convertedFile;
                $imageToResize = self::convertToJpg($imageToResize, $convertedFilePath);
                $finalDoc = $convertedFile;
            }


            //Resize image if width is greater than 1200
            try {
                $imageWidth = self::getImageWidth($imageToResize);
                if ($imageWidth > 1200) {
                    $finalDoc = self::resizeImage($imageToResize);
                }
                return $finalDoc;
            } catch (Exception $e) {
                return $finalDoc;
            }
        }

        return $finalDoc;
    }

    /*
     * function for upload AWS S3
     */

    public static function uploadFilesAWS($file) {
        $imageFile = '';
        $imagePath = config('common.resource_paths.images');
        $thumbPath = config('common.resource_paths.thumb');

        if ($file) {
            $imageFile = self::uploadFiles($file, $imagePath, false);
            $mimeType = $file->getClientMimeType();
            if ($mimeType != 'image/png') {
                self::createImageThumbnail($imagePath, $imageFile, $thumbPath);
            } else {
                self::createThumbPng($imagePath, $imageFile, $thumbPath);
            }

            $s3 = \Storage::disk('s3');
            $s3->put($imageFile, file_get_contents($imagePath . $imageFile), 'public');
            $s3->put('thumb/' . $imageFile, file_get_contents(config('common.resource_paths.thumb') . $imageFile), 'public');
            $region = env('AWS_REGION');
            $s3folder = env('AWS_BUCKET');
            $S3path = "https://s3.$region.amazonaws.com/$s3folder/thumb/";

            $uploadedImagePath = $imagePath . $imageFile;
            $uploadedThumbPath = $thumbPath . $imageFile;

            if (\File::exists($uploadedImagePath)) {
                unlink($uploadedImagePath);
            }

            if (\File::exists($uploadedThumbPath)) {
                unlink($uploadedThumbPath);
            }

            return $S3path . $imageFile;
        }


        return $imageFile;
    }

    /*
     * create thumb for aws s3
     */

    public static function createThumb($fileName, $file) {
        $thumbDir = config('common.resource_paths.thumb');
        $file->move($thumbDir, $fileName);
        $thumbnailToResize = $thumbDir . $fileName;
        $imageWidth = self::getImageWidth($thumbnailToResize);
        //Get image height
        $imageHeight = self::getImageHeight($thumbnailToResize);
        $imageToCreateThumb = imagecreatetruecolor(150, 150);
        $thumbImage = imagecreatefromjpeg($thumbnailToResize);
        imagecopyresampled($imageToCreateThumb, $thumbImage, 0, 0, 0, 0, 150, 150, $imageWidth, $imageHeight);
        // Output
        imagejpeg($imageToCreateThumb, $thumbnailToResize, 100);
        return true;
    }

    public static function createImageThumbnail($existingImageDir, $existingImageFile, $thumbnailDir) {
        try {
            $thumbnailToResize = $thumbnailDir . $existingImageFile;
            //Copy original image to thumbnail folder
            File::copy($existingImageDir . $existingImageFile, $thumbnailToResize);
            //Get image width
            $imageWidth = self::getImageWidth($thumbnailToResize);
            //Get image height
            $imageHeight = self::getImageHeight($thumbnailToResize);

            $imageToCreateThumb = imagecreatetruecolor(150, 150);
            $thumbImage = imagecreatefromjpeg($thumbnailToResize);
            imagecopyresampled($imageToCreateThumb, $thumbImage, 0, 0, 0, 0, 150, 150, $imageWidth, $imageHeight);
            // Output
            imagejpeg($imageToCreateThumb, $thumbnailToResize, 100);

            return $existingImageFile;
        } catch (Exception $e) {
            $thumbnailToResize = $thumbnailDir . $existingImageFile;
            //Copy original image to thumbnail folder
            File::copy($existingImageDir . $existingImageFile, $thumbnailToResize);
            return $existingImageFile;
        }
    }

    /**
     * function for resize PNG File.
     * @param $existingImageDir
     * @param $existingImageFile
     * @param $thumbnailDir
     * @return mixed
     */
    public static function createThumbPng($existingImageDir, $existingImageFile, $thumbnailDir) {
        try {

            $thumbnailToResize = $thumbnailDir . $existingImageFile;
            //Copy original image to thumbnail folder
            File::copy($existingImageDir . $existingImageFile, $thumbnailToResize);
            $img = Image::make($thumbnailToResize);
            $img->resize(150, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save();
            return $existingImageFile;
        } catch (Exception $e) {
            $thumbnailToResize = $thumbnailDir . $existingImageFile;
            //Copy original image to thumbnail folder
            File::copy($existingImageDir . $existingImageFile, $thumbnailToResize);
            return $existingImageFile;
        }
    }

    public static function createImageThumbnailSize($existingImageDir, $existingImageFile, $thumbnailDir, $width, $height) {
        $thumbnailToResize = $thumbnailDir . $existingImageFile;

        //Copy original image to thumbnail folder
        File::copy($existingImageDir . $existingImageFile, $thumbnailToResize);

        //Get image width
        $imageWidth = self::getImageWidth($thumbnailToResize);

        //Get image height
        $imageHeight = self::getImageHeight($thumbnailToResize);

        $imageToCreateThumb = imagecreatetruecolor($width, $height);
        $thumbImage = imagecreatefromjpeg($thumbnailToResize);
        imagecopyresampled($imageToCreateThumb, $thumbImage, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight);

        // Output
        imagejpeg($imageToCreateThumb, $thumbnailToResize, 100);

        return $existingImageFile;
    }

    /**
     * Check if given mime is available in allowed mimes list
     * @param $mime
     * @return bool
     */
    public static function checkImageMimes($mime) {
        $mimes = array('image/jpeg',
            'image/jpg',
            'image/bmp',
            'image/png'
        );

        if (in_array($mime, $mimes)) {
            return true;
        }
        return false;
    }

    /**
     * function for add http in url
     * @param $url
     * @return string
     */
    public static function addHttpToUrl($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            return $url = "http://" . $url;
        } else {
            return $url;
        }
    }

    /**
     * Get file extension
     * @param $fileName
     * @return mixed
     */
    public static function getFileExtension($fileName) {
        $fileExtension = explode('.', $fileName);
        return $fileExtension[count($fileExtension) - 1];
    }

    /**
     * function for get file type
     * @param $fileName
     * @return mixed
     */
    public static function getUploadedFileType($fileName) {
        $extension = self::getFileExtension($fileName);
        return self::getFileType($extension);
    }

    /**
     * Get file MIME type
     * @param $extension
     * @return bool
     */
    public static function getMIMEType($extension) {
        $mimeTypes = array('png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'wav' => 'audio/wav',
            'mpv' => 'audio/mpeg',
            'mpg' => 'audio/mpeg'
        );

        if (array_key_exists($extension, $mimeTypes)) {
            return $mimeTypes[$extension];
        }
        return false;
    }

    /**
     * Get file type by extension
     * @param $extension
     */
    public static function getFileType($extension) {
        switch ($extension) {
            case 'doc':
                return 'word';
                break;

            case 'docx':
                return 'word';
                break;

            case 'xls':
                return 'excel';
                break;

            case 'xlsx':
                return 'excel';
                break;

            case 'ppt':
                return 'ppt';
                break;

            case 'pptx':
                return 'ppt';
                break;

            case 'pdf':
                return 'pdf';
                break;

            case 'txt':
                return 'text';
                break;

            case 'jpg':
                return 'image';
                break;

            case 'jpeg':
                return 'image';
                break;

            case 'png':
                return 'image';
                break;

            case 'bmp':
                return 'image';
                break;

            case 'gif':
                return 'image';
                break;

            case 'mp3':
                return 'audio';
                break;

            case 'wav':
                return 'audio';
                break;

            case 'mp4':
                return 'video';
                break;

            case 'flv':
                return 'video';
                break;

            case 'mpeg':
                return 'video';
                break;

            case '3gp':
                return 'video';
                break;

            case 'mkv':
                return 'video';
                break;

            case 'webm':
                return 'video';
                break;

            case 'avi':
                return 'video';
                break;

            case 'vob':
                return 'video';
                break;

            case 'ogg':
                return 'video';
                break;

            case 'ogv':
                return 'video';
                break;

            case 'mpv':
                return 'video';
                break;

            case 'mpg':
                return 'video';
                break;

            default:
                return 'text';
        }
    }

    /**
     * @param $date
     * Format date as ago
     */
    public static function formatDateAgo($date) {
        if ($date) {
            return Carbon::createFromTimestamp(strtotime($date))->diffForHumans();
        } else {
            return $date;
        }
    }

    /**
     * Format Date
     * @param $date
     * @return formatted date
     */
    public static function formatDate($date, $not_available = true) {
        if ($date) {
            return date(config('app.date_format_php'), strtotime($date));
        } else {
            if ($not_available == false) {
                return '';
            }
            return 'N/A';
        }
    }

    /**
     * Format Date
     * @param $date
     * @return formatted date
     */
    public static function formatDateTime($date, $not_available = true) {
        if ($date) {
            return date(config('app.date_time_format_php'), strtotime($date));
        } else {
            if ($not_available == false) {
                return '';
            }
            return 'N/A';
        }
    }

    /**
     * Format Date
     * @param $date
     * @return formatted date
     */
    public static function formatDateWithDash($date) {
        if ($date) {
            return date("j-M-Y", strtotime($date));
        } else {
            return $date;
        }
    }

    /**
     * Show error page
     * @return \Illuminate\Http\Response
     */
    public static function showErrorPage() {
        return response()->view('errors.error', [], 500);
    }

    /*
     * common function for upload image and create thumb
     */

    public static function uploadAndCreateThumb($image, $path, $thumbPath) {
        $imageFile = '';
        if ($image) {
            $imageFile = self::uploadFiles($image, $path);
            self::createImageThumbnail($path, $imageFile, $thumbPath);
        }
        return $imageFile;
    }

    /*
     * function for send notification on android device.
     */

    public static function notificationAndroid($notificationType, $notifcationMessage, $notifcationInfo, $deviceToken, $additionalData) {
        $GOOGLE_API_KEY = config('app.server_key_android');
        $url = config('app.fcm_url');
        $regId = $deviceToken;
        $response = array("notification_type" => $notificationType, "notification_info" => $notifcationInfo, "message" => $notifcationMessage);
        $GcmRegistrationId = trim($regId);
        $GcmRegistrationId = array($GcmRegistrationId);
        //  $fields = array('registration_ids' => $GcmRegistrationId, 'data' => $response);
        $fields = array(
            'registration_ids' => $GcmRegistrationId,
            'notification' => array('body' => $notifcationMessage),
            'data' => array('message' => $notifcationMessage, 'sound' => 'default', 'senderName' => $additionalData['senderName'], 'companyName' => $additionalData['companyName'])
        );

        $headers = array('Authorization: key=' . $GOOGLE_API_KEY, 'Content-Type: application/json');

        // Open connection
        $ch = curl_init();                  // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        // Execute post
        $result = curl_exec($ch);
        return $result;
    }

    /*
     * function for send notification on IOS device
     */

    public static function sendNotificationIos($notificationType = '', $notifcationMessage = '', $notifcationInfo = '', $deviceTokens = '', $additionalData) {
        if (!empty($deviceTokens)) {
            $token = $deviceTokens;
            $ctx = stream_context_create();
            $passphrase = 'sportsman';
            $path = '/var/www/html/public/pemfile/apns-sportsman-prod.pem';
            stream_context_set_option($ctx, 'ssl', 'local_cert', $path);
            stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
            /* $fp = stream_socket_client(
              'ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx); */
            $fp = stream_socket_client(
                    'ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

            if (!$fp) {
                exit("Failed to connect: $err $errstr" . PHP_EOL);
            }
            $ausgabe = '';
            // $message = $message;
            $ausgabe .= 'Connected to APNS' . PHP_EOL . '<hr>';

            $response = array("notification_type" => $notificationType, "notification_info" => $notifcationInfo);
            $body['aps'] = array(
                'alert' => $notifcationMessage,
                'sound' => 'default',
                'senderName' => $additionalData['senderName'],
                'companyName' => $additionalData['companyName'],
                'identifire' => $response
            );
            $payload = json_encode($body);
            // Build the binary notification
            $msg = chr(0) . @pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
// Send it to the server
            try {
                $write_result = fwrite($fp, $msg, strlen($msg));
                if (!$write_result) {
                    $ausgabe .= 'Message to not delivered' . PHP_EOL . '<hr>';
                } else {
                    $ausgabe .= 'Message to successfully delivered' . PHP_EOL . '<hr>';
                }
            } catch (\Exception $e) {
                echo('Error sending payload: ' . $e->getMessage());
                sleep(5);
            }
            $ausgabe .= 'close connection';
            fclose($fp);
            return 1;
        }
    }

    public static function convertFilterDate($keyword) {
        $date = '';
        try {
            if (\Carbon\Carbon::createFromFormat(config('app.date_format_php'), $keyword) !== false) {
                $date = self::convertDate($keyword);
            }
        } catch (Exception $e) {
            $date = '';
        }
        return $date;
    }

    /**
     * Method for getting counts of objects by value 
     *
     * @param $array
     * @return array
     */
    public static function arrayGetCountOfBrands($array, $brandId) {
        $result = array();
        $count = 0;
        foreach ($array as $key => $value) {
            if ($value->brand_id == $brandId) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * function for display amount
     * @param $amount
     * @return int|string
     */
    public static function currency($amount) {
        if($amount == ''){
            $amount = 0;
            $amount = '$'.number_format($amount,2);
        } elseif ($amount < 0){
            $amount = -($amount);
            $amount = '-$'.number_format($amount,2);
        }else{
            $amount = '$'.number_format($amount,2);
        }

        return $amount;
    }

    /**
     * function for remove .00 from amount
     * @param $price
     * @return bool|string
     */
    public static function formatDBAmount($price) {
        $price = substr($price, -3) == ".00" ? substr($price, 0, -3) : $price;
        return $price;
    }

    /**
     * function for merge two arrays
     * @param $array1
     * @param $array2
     * @return array
     */
    public static function mergeTwoArrays($array1, $array2){
        $array = array_merge ($array1, $array2);
        $newArray = array_unique($array, SORT_REGULAR);
        return array_values($newArray);
    }

    /**
     * function for calculate percentage
     * @param $current
     * @param $total
     * @return float
     */
    public static function calculatePercentage($current, $total){
        $percentage = ($current / $total) * 100;
        return round($percentage, 2);
    }

}
