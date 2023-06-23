import os
import subprocess
import multiprocessing
import sys
import json


def main():
    input_args = json.loads(sys.argv[1])
    input_file = input_args['input_file']
    output_formats = input_args['output_formats']
    args_list = process_videos(input_file, output_formats)

    with multiprocessing.Pool() as pool:
        output_files_list = pool.map(encode_video, args_list)

    print(output_files_list)

def encode_video(args):
    input_file, output_format, resolution, codec, format = args

    output_file = os.path.join(
        os.path.dirname(input_file),
        f"{os.path.splitext(input_file)[0]}_{resolution['resolution']}_{codec}.{format}"
    )

    input_args = (
        f"-i {input_file} -c:v {codec} -b:v {resolution['bitrate']}k "
        f"-vf scale=w={get_width(resolution['resolution'])}:h={get_height(resolution['resolution'])} {output_file}"
    )
    ffmpeg_cmd = f"ffmpeg -y {input_args}"
    subprocess.call(ffmpeg_cmd, shell=True)

    return output_file


def process_videos(input_file, output_formats):
    args_list = []
    for output_format in output_formats:
        codec = output_format['codec']
        for resolution in output_format['resolutions']:
            format = output_format['format']
            args_list.append((input_file, output_format, resolution, codec, format))

    return args_list


def get_width(resolution):
    if resolution == '1080p':
        return 1920
    elif resolution == '720p':
        return 1280
    elif resolution == '480p':
        return 854
    else:
        return 0


def get_height(resolution):
    if resolution == '1080p':
        return 1080
    elif resolution == '720p':
        return 720
    elif resolution == '480p':
        return 480
    else:
        return 0


if __name__ == "__main__":
    main()
