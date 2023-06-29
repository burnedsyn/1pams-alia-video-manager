import os
import subprocess
import multiprocessing
import sys
import json
import logging

def main():
    input_args = json.loads(sys.argv[1])
    input_file = input_args['input_file']
    log_file = os.path.join(os.path.dirname(input_file), "conversion.log")

    # Configure logging
    logging.basicConfig(filename=log_file, level=logging.INFO, format='%(asctime)s; %(levelname)s; %(message)s')
    logger = logging.getLogger()

    logger.info(f"Process: start : {input_file}")
    output_formats = input_args['output_formats']
    product_id = input_args['product_id']
    args_list = process_videos(input_file, output_formats, product_id)
    # Generate thumbnail
    thumbnail_path = os.path.join(os.path.dirname(input_file), "thumbnail.jpg")
    generate_thumbnail(input_file, thumbnail_path)
    with multiprocessing.Pool() as pool:
        output_files_list = pool.map(encode_video, args_list)

    print(output_files_list)

    # Generate HLS data and video chunks
    generate_hls(input_file, output_formats)
    generate_hls_manifest(input_file, output_formats)

    # Generate DASH data and video chunks
    generate_dash(input_file, output_formats)
    generate_dash_manifest(input_file, output_formats)

    logger.info(f"Process:done: {input_file}")

def generate_thumbnail(input_file, thumbnail_path):
    # Set up logging
    logging.basicConfig(filename='conversion.log', level=logging.INFO, format='%(asctime)s; %(levelname)s; %(message)s')
    logger = logging.getLogger()

    # Use FFmpeg to extract the thumbnail
    thumbnail_time = "00:00:05"  # Specify the time for the thumbnail (e.g., 5 seconds)
    ffmpeg_cmd = f'ffmpeg -i "{input_file}" -ss {thumbnail_time} -vframes 1 "{thumbnail_path}"'

    try:
        subprocess.call(ffmpeg_cmd, shell=True)
        logger.info(f"Thumbnail generated: {thumbnail_path}")
    except Exception as e:
        logger.error(f"Failed to generate thumbnail: {str(e)}")


def encode_video(args):
    input_file, output_format, resolution, codec, format = args

    output_file = os.path.join(os.path.dirname(input_file),
                               f"{os.path.splitext(input_file)[0]}_{resolution['resolution']}_{codec}.{format}")
    log_file = os.path.join(os.path.dirname(input_file), "conversion.log")

    # Configure logging
    logging.basicConfig(filename=log_file, level=logging.INFO, format='%(asctime)s; %(levelname)s; %(message)s')
    logger = logging.getLogger()

    logger.info(f"Conversion:start: {input_file} to {output_file}")

    input_args = (
        f"-i \"{input_file}\" -c:v {codec} -b:v {resolution['bitrate']}k "
        f"-vf scale=w={get_width(resolution['resolution'])}:h={get_height(resolution['resolution'])} \"{output_file}\""
    )
    ffmpeg_cmd = f"ffmpeg -y {input_args}"

    try:
        subprocess.call(ffmpeg_cmd, shell=True)
        logger.info(f"Conversion:done: {input_file} to {output_file}")
        return output_file
    except Exception as e:
        logger.error(f"Conversion:error: {input_file} to {output_file}. Error message: {str(e)}")
        return None

def process_videos(input_file, output_formats, product_id):
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

def generate_hls_or_dash(input_file, output_formats, is_hls=True):
    main_directory = os.path.dirname(input_file)
    log_file = os.path.join(main_directory, "conversion.log")

    # Configure logging
    logging.basicConfig(filename=log_file, level=logging.INFO, format='%(asctime)s; %(levelname)s; %(message)s')
    logger = logging.getLogger()

    # Determine the output directory based on the format
    output_directory = "HLS" if is_hls else "DASH"
    output_directory = os.path.join(main_directory, output_directory)

    # Create the output directory if it doesn't exist
    if not os.path.exists(output_directory):
        os.makedirs(output_directory)

    # Iterate over each output format
    for output_format in output_formats:
        # Get the codec, resolutions, and format for the output format
        codec = output_format['codec']
        resolutions = output_format['resolutions']
        format = output_format['format']

        # Iterate over each resolution
        for resolution in resolutions:
            # Generate the output file path
            base_file_name = os.path.splitext(os.path.basename(input_file))[0]
            output_file = os.path.join(output_directory,
                                       f"{base_file_name}_{resolution['resolution']}.{format}")

            # Generate the command to create the playlist and video chunks
            args = (
                f"-i \"{input_file}\" -c:v {codec} -b:v {resolution['bitrate']}k "
                f"-vf scale=w={get_width(resolution['resolution'])}:h={get_height(resolution['resolution'])} "
            )
            if is_hls:
                args += f"-hls_time 10 -hls_list_size 0 -f hls \"{output_file}\""
            else:
                args += f"-dash 1 -hls_playlist 0 \"{output_file}\""

            ffmpeg_cmd = f"ffmpeg -y {args}"

            if is_hls:
                logger.info(f"HLS generation:start: for resolution {resolution['resolution']}")
            else:
                logger.info(f"DASH generation:start: for resolution {resolution['resolution']}")

            # Execute the command to create the playlist and video chunks
            try:
                subprocess.call(ffmpeg_cmd, shell=True)
                if is_hls:
                    logger.info(f"HLS generation:done: for resolution {resolution['resolution']}")
                else:
                    logger.info(f"DASH generation:done: for resolution {resolution['resolution']}")
            except Exception as e:
                if is_hls:
                    logger.error(f"HLS generation:error: for resolution {resolution['resolution']}. Error message: {str(e)}")
                else:
                    logger.error(f"DASH generation:error: for resolution {resolution['resolution']}. Error message: {str(e)}")

def generate_hls(input_file, output_formats):
    generate_hls_or_dash(input_file, output_formats, is_hls=True)

def generate_dash(input_file, output_formats):
    generate_hls_or_dash(input_file, output_formats, is_hls=False)

def generate_hls_manifest(input_file, output_formats):
    main_directory = os.path.dirname(input_file)
    output_directory = os.path.join(main_directory, "HLS")
    manifest_file = os.path.join(output_directory, "playlist.m3u8")

    resolutions = []
    for output_format in output_formats:
        resolutions.extend(output_format['resolutions'])

    with open(manifest_file, 'w') as f:
        f.write("#EXTM3U\n")
        for resolution in resolutions:
            resolution_file = os.path.join(output_directory,
                                           f"{os.path.splitext(os.path.basename(input_file))[0]}_{resolution['resolution']}.{output_format['format']}")
            f.write(f"#EXT-X-STREAM-INF:BANDWIDTH={resolution['bitrate']}000,RESOLUTION={resolution['resolution']}\n")
            f.write(f"{os.path.basename(resolution_file)}\n")

def generate_dash_manifest(input_file, output_formats):
    main_directory = os.path.dirname(input_file)
    output_directory = os.path.join(main_directory, "DASH")
    manifest_file = os.path.join(output_directory, "manifest.mpd")

    resolutions = []
    for output_format in output_formats:
        resolutions.extend(output_format['resolutions'])

    with open(manifest_file, 'w') as f:
        f.write('<?xml version="1.0" encoding="utf-8"?>\n')
        f.write('<MPD xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:mpeg:dash:schema:mpd:2011" xsi:schemaLocation="urn:mpeg:dash:schema:mpd:2011 DASH-MPD.xsd" profiles="urn:mpeg:dash:profile:isoff-on-demand:2011" type="static">\n')
        f.write('\t<Period>\n')
        for resolution in resolutions:
            resolution_file = os.path.join(output_directory,
                                           f"{os.path.splitext(os.path.basename(input_file))[0]}_{resolution['resolution']}.{output_format['format']}")
            f.write('\t\t<AdaptationSet mimeType="video/mp4" segmentAlignment="true" startWithSAP="1" maxWidth="')
            f.write(f"{get_width(resolution['resolution'])}" + '" maxHeight="' + f"{get_height(resolution['resolution'])}" + '" par="16:9">\n')
            f.write('\t\t\t<Representation bandwidth="' + f"{resolution['bitrate']}000" + '" width="' + f"{get_width(resolution['resolution'])}" + '" height="' + f"{get_height(resolution['resolution'])}" + '">\n')
            f.write('\t\t\t\t<BaseURL>' + os.path.basename(resolution_file) + '</BaseURL>\n')
            f.write('\t\t\t\t<SegmentBase indexRangeExact="true">\n')
            f.write('\t\t\t\t\t<Initialization range="0-107" />\n')
            f.write('\t\t\t\t</SegmentBase>\n')
            f.write('\t\t\t</Representation>\n')
            f.write('\t\t</AdaptationSet>\n')
        f.write('\t</Period>\n')
        f.write('</MPD>\n')

if __name__ == "__main__":
    main()