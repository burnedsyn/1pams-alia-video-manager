import os
import json
from ovh import Client

def upload_to_ovh(directory_path, exclude_filename=None):
    # OVH credentials
      // OVH API credentials
        $accessKeyId = '72382137a8064638a1ebd8ae19f9f3d3';
        $secretAccessKey = '86d6ddece3924d2b9f3b3adda97112a1';
        $region = 'gra'; // e.g., ''
        //$consumerKey = 'user-YDWtFgwJrUXC'; 
        // Create a new S3Client instance
    endpoint = '<OVH_ENDPOINT>'
    application_key = '<APPLICATION_KEY>'
    application_secret = '<APPLICATION_SECRET>'
    consumer_key = '<CONSUMER_KEY>'

    # Connect to OVH Public Cloud
    client = Client(endpoint, application_key, application_secret, consumer_key)

    # Store the URLs of the uploaded objects
    uploaded_urls = []

    # Iterate over files and directories in the specified directory
    for root, dirs, files in os.walk(directory_path):
        # Exclude the specified filename from the upload
        if exclude_filename:
            files = [f for f in files if f != exclude_filename]

        for file in files:
            file_path = os.path.join(root, file)
            object_name = os.path.relpath(file_path, directory_path)
            object_name = object_name.replace("\\", "/")  # Convert backslashes to forward slashes

            # Upload the file to OVH Public Cloud
            client.put(f'<CONTAINER_NAME>/{object_name}', open(file_path, 'rb').read())

            # Get the URL of the uploaded object
            container_url = client.get_container_url('<CONTAINER_NAME>')
            object_url = f'{container_url}/{object_name}'
            uploaded_urls.append(object_url)

            print(f"Uploaded {file_path} as {object_name}")
            print(f"URL: {object_url}")

        for dir in dirs:
            dir_path = os.path.join(root, dir)
            object_name = os.path.relpath(dir_path, directory_path)
            object_name = object_name.replace("\\", "/") + '/'  # Convert backslashes to forward slashes

            # Create a directory object in OVH Public Cloud
            client.put(f'<CONTAINER_NAME>/{object_name}', '')

            # Get the URL of the created directory
            container_url = client.get_container_url('<CONTAINER_NAME>')
            object_url = f'{container_url}/{object_name}'
            uploaded_urls.append(object_url)

            print(f"Created directory {dir_path} as {object_name}")
            print(f"URL: {object_url}")

    # Convert the URL array to JSON
    url_array_json = json.dumps(uploaded_urls)

    # Print the JSON to stdout (captured by the PHP script)
    print(url_array_json)


# Retrieve the directory and exclude file from command-line arguments
import sys
directory = sys.argv[1]
exclude_file = sys.argv[2] if len(sys.argv) > 2 else None

upload_to_ovh(directory, exclude_file)
