import os
import subprocess
from multiprocessing import Pool
import sys
import json
import getpass



def main():
    if len(sys.argv) < 2:
        print("No input JSON provided.")
        return
    
    input_json = sys.argv[1]
    input_array = json.loads(input_json)

    if 'output_formats' not in input_array:
        print("No output_formats found in input JSON.")
        return

    output_formats = input_array['output_formats']
    
    process_videos(input_array, output_formats)

def encode_video(args):
    input_array, format, resolution, codec = args
    input_file = input_array['input_file']
    output_dir = os.path.dirname(input_file)
    output_log = os.path.splitext(input_file)[0] + '.log'
    log_file = os.path.join(output_dir, output_log)
    output_formats=input_array['output_formats']
       
    # Prepare the log file
    with open(log_file, "w"):
        pass

    # Set the log file permissions to 775
    os.chmod(log_file, 0o775)
    username = getpass.getuser()
    current_directory = os.getcwd()
    # Report the start of the ffmpeg process with its PID
    process_id = os.getpid()
    start_message = f"in {current_directory}\n Started encoding: {input_file} (PID: {process_id})\n The current user is: {username}\n"
    with open(log_file, "a") as f:
        f.write(start_message)

    for res in resolution:
        output_file = os.path.join(output_dir, f"{os.path.splitext(input_file)[0]}_{resolution['resolution']}_{codec}.{format}")
        input_args = f"-i {input_file} -c:v {codec} -b:v {resolution['bitrate']}k -vf scale=w={get_width(resolution['resolution'])}:h={get_height(resolution['resolution'])} {output_file}"
        ffmpeg_cmd = f"ffmpeg -y {input_args}"
        # Execute the ffmpeg command and capture the output
        #retourcmd=subprocess.call(ffmpeg_cmd, shell=True, stdout=sys.stdout, stderr=subprocess.STDOUT)
        with open(log_file, "w") as f:
            subprocess.call(ffmpeg_cmd, shell=True, stdout=f, stderr=subprocess.STDOUT)


    # Report the completion of the ffmpeg process
    completion_message = f"Finished encoding: {input_file} (PID: {process_id})\n "
    with open(log_file, "a") as f:
        f.write(completion_message)


    
def process_videos(input_file, output_formats):
    args_list = []
    for format in output_formats:
        for resolution in format['resolutions']:
            args_list.append((input_file, format['format'], resolution, format['codec']))
    
    pool = Pool()
    pool.map(encode_video, args_list)
    pool.close()
    pool.join()
def get_width(resolution):
    if resolution == '1080p':
        return 1920
    elif resolution == '720p':
        return 1280
    elif resolution == '480p':
        return 854
    else:
        raise Exception(f"Unsupported resolution: {resolution}")

def get_height(resolution):
    if resolution == '1080p':
        return 1080
    elif resolution == '720p':
        return 720
    elif resolution == '480p':
        return 480
    else:
        raise Exception(f"Unsupported resolution: {resolution}")

if __name__ == "__main__":
    main()