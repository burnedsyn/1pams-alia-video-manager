<?php
// includes/video-transcoder.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$dirToAdd = '/usr/bin/';

$currentPaths = ini_get('open_basedir');
$newPaths = $currentPaths . ':' . $dirToAdd;
ini_set('open_basedir', $newPaths);
require_once ALIA_VMS_PLUGIN_PATH . '/vendor/autoload.php';;



class Alia_VMS_Transcoder
{
    private $ffmpeg;
    private $outputFormats;
    private $timeLine;
    public function __construct()
    {

        $this->outputFormats = [
            [
                'format' => 'mp4',
                'codec' => 'libx264',
                'resolutions' => [
                    ['resolution' => '1080p', 'bitrate' => '4000'],
                    ['resolution' => '720p', 'bitrate' => '2500'],
                    ['resolution' => '480p', 'bitrate' => '1000'],
                ]
            ],


        ];
    }


    public function transcode_vimeo_import($inputFile, $product_id){
        //create the log file
        $outputDir = dirname($inputFile);
        $outputlog = pathinfo($inputFile, PATHINFO_DIRNAME) . '/1pams.log';
        
        // Convert the PHP data to a JSON string
        $inputJson = json_encode([
            'input_file' => $inputFile,
            'output_formats' => $this->outputFormats,
            'product_id' => $product_id
        ]);
        // Execute the Python script as a separate process
        $pythonScript = dirname(__FILE__) . "/transcoder4.py";
        $descriptorspec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w']  // stderr
        ];
        $command = "python3 $pythonScript '$inputJson'  > /dev/null 2>&1 &";
        $process = proc_open($command, $descriptorspec, $pipes);

        // Close the pipes to stdin, stdout, and stderr
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        // Get the process ID
        $pid = proc_get_status($process)['pid'];

        // Close the process handle
        proc_close($process);

        // Continue with PHP script execution

        file_put_contents($outputlog, "$product_id\n");

        return $outputlog;


    }


    public function transcode_video($inputFile, $product_id)
    {
        $outputDir = dirname($inputFile);
        $outputlog = pathinfo($inputFile, PATHINFO_DIRNAME) . '/1pams.log';
        




        // Convert the PHP data to a JSON string
        $inputJson = json_encode([
            'input_file' => $inputFile,
            'output_formats' => $this->outputFormats,
            'product_id' => $product_id
        ]);

        // Execute the Python script as a separate process
        $pythonScript = dirname(__FILE__) . "/transcoder4.py";
        echo $pythonScript;

        $command = "python3 $pythonScript '$inputJson'  > /dev/null 2>&1 &";
        echo ("<br>$command");
        
        $descriptorspec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w']  // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        // Close the pipes to stdin, stdout, and stderr
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        // Get the process ID
        $pid = proc_get_status($process)['pid'];

        // Close the process handle
        proc_close($process);

        // Continue with PHP script execution

        file_put_contents($outputlog, "$product_id\n");
        sleep(5);
        wp_redirect(home_url()."/wp-admin/admin.php?page=alia-vms-video&tab=tab1&conversion=" . $outputDir);
        exit;  // Make sure to exit after sending the redirect header

    }
    public function get_vidStatus($outputDir)
    {

        $pamslog = $this->create_vidStatus($outputDir);

        return  $pamslog;
    }

    private function create_vidStatus($outputDir)
    {
        $outputDir = urldecode($outputDir);
        $outputlog = $outputDir . '/conversion.log';
        $pamsLogPath = $outputDir . '/1pams.log';
        $logData = file_get_contents($outputlog);
        $pamsdata = file_get_contents($pamsLogPath);



?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Process product <?= $pamsdata ?></h3>
            </div>
            <div class="card-body">
            
                <?php
                $result = array();

                $logs = explode("\n", $logData);
                foreach ($logs as $log) {
                    $logParts = explode(';', $log);
                    $result['date'] = isset($logParts[0]) ? trim($logParts[0]) : '';
                    $result['level'] = isset($logParts[1]) ? trim($logParts[1]) : '';
                    $message = isset($logParts[2]) ? $logParts[2] : '';
                    $workParts = explode(':', $message);

                    $result['operation'] = isset($workParts[0]) ? trim($workParts[0]) : '';
                    $result['opStatus'] = isset($workParts[1]) ? trim($workParts[1]) : '';
                    $result['data'] = isset($workParts[2]) ? trim($workParts[2]) : '';
                    $currentconv = 0;
                    switch ($result['opStatus']) {
                        case 'start':
                            switch ($result['operation']) {

                                case "Process":
                                    $file = pathinfo($result['data'], PATHINFO_FILENAME) . '.' . pathinfo($result['data'], PATHINFO_EXTENSION);
                                    $timeLine['Process'] = array();
                                    $timeLine['Process']['product'] = $pamsdata;
                                    $timeLine['Process']['file'] = $file;
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine['Process']['start'] = $time;
                                    $timeLine['Process']['status'] = $result['opStatus'];

                                    break;
                                case "Conversion":
                                    $resultdata = explode('to', $result['data']);
                                    $source = pathinfo($resultdata[0], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[0], PATHINFO_EXTENSION);
                                    $file = pathinfo($resultdata[1], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[1], PATHINFO_EXTENSION);
                                    $tdata = explode("_", $file);
                                    $i = $tdata[1];
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine['Conversion']["$i"]['file'] = $file;
                                    $timeLine['Conversion']["$i"]['start'] = $time;

                                    $timeLine['Conversion']["$i"]['status'] = $result['opStatus'];

                                    break;
                                case "HLS generation":
                                    $resultdata = explode(' ', $result['data']);
                                    $i = $resultdata[2];
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine["HLS generation"][$i]['start'] = $time;
                                    $timeLine["HLS generation"][$i]['status'] = $result['opStatus'];
                                    break;
                                case "DASH generation":
                                    $resultdata = explode(' ', $result['data']);
                                    $i = $resultdata[2];
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine["DASH generation"][$i]['start'] = $time;
                                    $timeLine["DASH generation"][$i]['status'] = $result['opStatus'];
                                    break;
                            } //switch OPERATION
                            break;
                        case 'done':
                            switch ($result['operation']) {

                                case "Process":
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine['Process']['end'] = $time;
                                    $timeLine['Process']['status'] = $result['opStatus'];

                                    break;
                                case "Conversion":
                                    $resultdata = explode('to', $result['data']);
                                    $source = pathinfo($resultdata[0], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[0], PATHINFO_EXTENSION);
                                    $file = pathinfo($resultdata[1], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[1], PATHINFO_EXTENSION);
                                    $tdata = explode("_", $file);
                                    $i = $tdata[1];
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine['Conversion']["$i"]['end'] = $time;
                                    $timeLine['Conversion']["$i"]['status'] = $result['opStatus'];
                                    $timeLine['Conversion']["$i"]['data'] = $result['data'];
                                    break;
                                case "HLS generation":
                                    $resultdata = explode(' ', $result['data']);
                                    $i = $resultdata[2];
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine["HLS generation"][$i]['end'] = $time;
                                    $timeLine["HLS generation"][$i]['status'] = $result['opStatus'];
                                    break;
                                case "DASH generation":
                                    $resultdata = explode(' ', $result['data']);
                                    $i = $resultdata[2];
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                                    $time = $datetime->format('H:i:s');
                                    $timeLine["DASH generation"][$i]['end'] = $time;
                                    $timeLine["DASH generation"][$i]['status'] = $result['opStatus'];
                                    break;
                            } //switch OPERATION
                            break;
                        case 'error':

                            break;
                    }
                } //foreach

                foreach ($timeLine as $index => $result) {
                    $this->getBlock($index, $result);
                }
                $test = $timeLine['Process']['status'];

                if ($test != "done") header("Refresh: 30");
                else {
                    $timeLine["Process"]["outputDir"] = $outputDir;
                    $this->timeLine = $timeLine;
                    $_SESSION['data'] = $timeLine;
                    wp_redirect(home_url().'/wp-admin/admin.php?page=alia-vms-video&tab=tab1&upload=start');
                    
                    return $timeLine;
                }

                ?>
                </div>
            
        </div>
        <?php
        //$this->getBlock($result);        
    } //createvidstatus



    function getBlock($index, $result)
    {


        switch ($index) {

            case "Process": 
                
                ?>
                <!-- Operation: Process -->

                <div class="card-item <?= $result['status'] ?>">
                    <p class="card-header"><?= $index ?> : <?= $result['file'] ?></p>
                    <span class="time">Start : <?= isset($result['start']) ? $result['start'] : '' ?> End : <?= isset($result['end']) ? $result['end'] : '' ?>
                    </span>
                    <div class="card-body">
                        <p>Product : <?= $result['product'] ?></p><br>
                        <p>Video file : <?= $result['file'] ?></p><br>
                        <p>Status:<?= $result['status'] ?> </p>

                    </div>
                </div>

                <?php
                
                break;
            case "Conversion":

                foreach ($result as $res => $value) {
                    if ($value['status'] =='start') echo("<div class=\"overlay dark\">
                <i class=\"fas fa-2x fa-sync-alt fa-spin\"></i>");
                ?>
                    <div class="card-item <?= $value['status'] ?>">
                        <p class="card-header"><?= $index ?> : <?= $res ?></p>
                        <span class="time">Start : <?= isset($value['start']) ? $value['start'] : '' ?> End : <?= isset($value['end']) ? $value['end'] : '' ?>
                        </span>
                        <div class="card-body">
                            <p>Status:<?= $value['status'] ?> </p>
                            <p>Video file : <?= $value['file'] ?></p>

                            <!-- <p>Data : <?= isset($value['data']) ? $value['data'] : ''  ?></p> -->

                        </div>
                    </div>
                <?php  
                 if ($value['status'] =='start') echo("</div>");         
                }
                break;
            case "HLS generation":
                foreach ($result as $res => $value) {
                    if ($value['status'] =='start') echo("<div class=\"overlay dark\">
                    <i class=\"fas fa-2x fa-sync-alt fa-spin\"></i>");
                ?>

                    <div class="card-item <?= $value['status'] ?>">
                        <p class="card-header">HLS <?= $res ?> </p>
                        <span class="time">Start : <?= isset($value['start']) ? $value['start'] : '' ?> End : <?= isset($value['end']) ? $value['end'] : '' ?>
                        </span>
                        <div class="card-body">
                            <p>Status:<?= $value['status'] ?> </p>

                        </div>
                    </div>

                <?php

if ($value['status'] =='start') echo("</div>");
                }
                break;
            case "DASH generation":
                foreach ($result as $res => $value) {
                    if ($value['status'] =='start') echo("<div class=\"overlay dark\">
                    <i class=\"fas fa-2x fa-sync-alt fa-spin\"></i>");
                ?>

                    <div class="card-item <?= $value['status'] ?>">
                        <p class="card-header">DASH <?= $res ?> </p>
                        <span class="time">Start : <?= isset($value['start']) ? $value['start'] : '' ?> End : <?= isset($value['end']) ? $value['end'] : '' ?>
                        </span>
                        <div class="card-body">
                            <p>Status:<?= $value['status'] ?> </p>

                        </div>
                    </div>

<?php
                    if ($value['status'] =='start') echo("</div>");

                }
                break;
        } //switch

    } //getBlock
}//fin declasse
