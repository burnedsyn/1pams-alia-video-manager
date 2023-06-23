<?php
// 1pams-vms/includes/video-transcoder.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$dirToAdd = '/usr/bin/';

$currentPaths = ini_get('open_basedir');
$newPaths = $currentPaths . ':' . $dirToAdd;
ini_set('open_basedir', $newPaths);
require_once ALIA_VMS_PLUGIN_PATH . '/vendor/autoload.php';;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\AOM_AV1;
use FFMpeg\Coordinate\Dimension;
use FFMPEG\Filter;

class Alia_VMS_Transcoder
{
    private $ffmpeg;
    private $outputFormats;

    public function __construct()
    {
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
        ]);
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

    /*
  [
                'format' => 'webm',
                'codec' => 'libvpx-vp9',
                'resolutions' => [
                    ['resolution' => '1080p', 'bitrate' => '3500'],
                    ['resolution' => '720p', 'bitrate' => '2000'],
                    ['resolution' => '480p', 'bitrate' => '800'],
                ]

            ],

 [
                'format' => 'mp4',
                'codec' => 'libaom-av1',
                'resolutions' => [
                    ['resolution' => '1080p', 'bitrate' => '3000k'],
                    ['resolution' => '720p', 'bitrate' => '1500k'],
                    ['resolution' => '480p', 'bitrate' => '700k'],
                ]
            ],

    */



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
        $pythonScript = dirname(__FILE__) . "/transcoder3.py";
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
        $viewlog = $this->get_vidStatus($outputDir);
        echo $viewlog;


        /* MODIF LAST
        
        //$video = $this->ffmpeg->open($inputFile);
        $logFile = $outputDir . $outputlog;
        // Prepare the log file
        $fp = fopen($logFile, "w");
        fclose($fp);

        foreach ($this->outputFormats as $format) {
            foreach ($format['resolutions'] as $resolution) {
                $outputFile = $outputDir . '/' . pathinfo($inputFile, PATHINFO_FILENAME) . '_' . $resolution['resolution'] . '_' . $format['codec'] . '.' . $format['format'];
                $inputArgs = "-i ".$inputFile." -c:v ".$format['codec']." -b:v ".$resolution['bitrate']."k -vf scale=w=".$this->get_width($resolution['resolution']).":h=".$this->get_height($resolution['resolution'])." ".$outputFile;
                $ffmpegCmd = "ffmpeg -y ".$inputArgs;
                $pid = pcntl_fork();
                if ($pid == -1) {
                    // Error forking child process
                } else if ($pid) {
                    // Parent process executes this block
                    // Close standard output and standard error streams so that the parent can continue executing.
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                } else {
                    // Child process executes this block
                    // Execute the FFMPEG command and redirect standard output and standard error streams to /dev/null to discard them.
                    $nullStream = fopen("/dev/null", "w");
                    $descriptorspec = array(
                        0 => array("pipe", "r"), // stdin is a pipe that the child will read from
                        1 => $nullStream, // stdout is redirected to /dev/null
                        2 => $nullStream // stderr is redirected to /dev/null
                    );
                    $process = proc_open($ffmpegCmd, $descriptorspec, $pipes);
                    proc_close($process); // Close the child process
                    exit(0); // Terminate the child process
                }
            }
        }

        $viewlog=$this->get_vidStatus($outputDir);
        echo $viewlog;
        echo("<a href='https://demo1.1proamonservice.be/wp-admin/admin.php?page=alia-vms-video&tab=tab1&conversion=".$_GET['conversion']."'> View status</a>");
            FIN MODIF LAST */
        /* foreach ($this->outputFormats as $format) {
            foreach ($format['resolutions'] as $resolution) {
                $outputFile = $outputDir . '/' . pathinfo($inputFile, PATHINFO_FILENAME) . '_' . $resolution['resolution'] . '_' . $format['codec'] . '.' . $format['format'];
                $videoFormat = $this->create_video_format($format['codec'], $resolution['bitrate']);

                try {
                    $dimension = new \FFMpeg\Coordinate\Dimension(
                        $this->get_width($resolution['resolution']),
                        $this->get_height($resolution['resolution'])
                    );

                    $video->filters()->resize($dimension)
                        ->synchronize();
                    $video->save($videoFormat, $outputFile);
                } catch (\Exception $e) {
                    echo 'Error: ' . $e->getMessage() . "\n";
                    continue;
                }
            }
        } */
    }
    public function get_vidStatus($outputDir)
    {


        $pamslog = $this->create_vidStatus($outputDir);

        return json_encode(array('status' => $pamslog));
    }

    private function create_vidStatus($outputDir)
    {
        $outputDir = urldecode($outputDir);
        $outputlog = $outputDir . '/conversion.log';
        $pamsLogPath = $outputDir . '/1pams.log';
        $logData = file_get_contents($outputlog);
        $pamsdata = file_get_contents($pamsLogPath);
        ?>
        <div class="timeline">
            <div class="timeline-header">
                <h3 class="timeline-title">Process product <?= $pamsdata ?></h3>
                <?php echo ("<a href='https://demo1.1proamonservice.be/wp-admin/admin.php?page=alia-vms-video&tab=tab1&conversion=" . $outputDir . "'> View status</a>"); ?>
            </div>
        <?php
        $result=array();
        
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
            $this->getBlock($result);

           
           
            /* switch($opStatus){
                case 'start':

                    break;
                case 'done':

                    break;
                case 'error':

                    break;
                } */

        } //foreach
                       
    }//createvidstatus

function getBlock($result){

    switch($result['operation']){

        case "Process": ?>
            <!-- Operation: Process -->
            <div class="timeline-item <?= $result['opStatus'] ?>">
                <span class="time"><?= $result['date'] ?></span>
                <h3 class="timeline-header">Operation:<?= $result['operation'] ?></h3>
                <div class="timeline-body">
                    <p>Status:<?= $result['opStatus'] ?> </p>
                    <p>Data:<?= $result['data']?></p>
                </div>
            </div>
        
        <?php
            break;
        case "Conversion": ?>
            <div class="timeline-item <?= $result['opStatus'] ?>">
                <span class="time"><?= $result['date'] ?></span>
                <h3 class="timeline-header">Operation:<?= $result['operation'] ?></h3>
                <div class="timeline-body">
                    <p>Status:<?= $result['opStatus'] ?> </p>
                    <p>Data:<?= $result['data']?></p>
                </div>
            </div>
       <?php
            break;
                
    }//switch

} //getBlock
}//fin declasse



/* function transcode_video($inputFile, $outputFolder, $baseFilename) {
    $ffmpeg = FFMpeg::create();
    $video = $ffmpeg->open($inputFile);

    $outputFormats = [
        [
            'format' => 'mp4',
            'codec' => 'libx264',
            'resolutions' => [
                ['resolution' => '1080p', 'bitrate' => '4000k'],
                ['resolution' => '720p', 'bitrate' => '2500k'],
                ['resolution' => '480p', 'bitrate' => '1000k'],
            ],
            'audioBitrate' => '96k',
        ],
        [
            'format' => 'webm',
            'codec' => 'libvpx-vp9',
            'resolutions' => [
                ['resolution' => '1080p', 'bitrate' => '3500k'],
                ['resolution' => '720p', 'bitrate' => '2000k'],
                ['resolution' => '480p', 'bitrate' => '800k'],
            ],
            'audioBitrate' => '96k',
        ],
        [
            'format' => 'mp4',
            'codec' => 'libaom-av1',
            'resolutions' => [
                ['resolution' => '1080p', 'bitrate' => '3000k'],
                ['resolution' => '720p', 'bitrate' => '1500k'],
                ['resolution' => '480p', 'bitrate' => '700k'],
            ],
            'audioBitrate' => '96k',
        ],
    ];

    foreach ($outputFormats as $formatConfig) {
        $format = $formatConfig['format'];
        $codec = $formatConfig['codec'];
        $resolutions = $formatConfig['resolutions'];
        $audioBitrate = $formatConfig['audioBitrate'];

        foreach ($resolutions as $resolutionConfig) {
            $resolution = $resolutionConfig['resolution'];
            $bitrate = $resolutionConfig['bitrate'];

            $outputFile = "{$outputFolder}/{$format}/{$baseFilename}-{$resolution}-{$codec}.{$format}";

            switch ($codec) {
                case 'libx264':
                    $videoFormat = new X264('aac');
                    break;
                case 'libvpx-vp9':
                    $videoFormat = new WebM();
                    break;
                case 'libaom-av1':
                    $videoFormat = new AOM_AV1();
                    break;
                default:
                    throw new Exception("Unsupported codec: {$codec}");
            }

            $videoFormat->setKiloBitrate($bitrate)
                        ->setAudioKiloBitrate($audioBitrate);

            $video->filters()
                  ->resize(new FFMpeg\Coordinate\Dimension($resolutionConfig['width'], $resolutionConfig['height']))
                  ->synchronize();

            $video->save($videoFormat, $outputFile);
        }
    }
} */
