# CertBot utility
<p align="center"><img src="https://gitlab.com/NovumGit/innovation-app-core/-/raw/master/assets/novum.png"  alt="Novum logo"/></p>

##What is this?

This is a component of the Novum Innovation app. It scans for plugins / sites that make use of SSL/http**s** and 
generates SSL certificates for them using the CertBot Docker container.

## Usage
The package adds a ```novum-certbot``` script to the vendor/bin folder. Invoke the command without any arguments to 
view all options. The script spins up a Docker container that will attempt to install SSL certificates. The resulting 
command will be something like this:

### Results
The command will exucute something in the order of this:
```
\#!/usr/bin/env bash

docker run -it --rm --name certbot \
      -v "$(pwd)/data/certbot:/data/certbot" \
      -v "$(pwd)/data/certbot:/etc/letsencrypt" \
      -v "/var/lib/letsencrypt:/var/lib/letsencrypt" \
      -p 80:80 \
      certbot/certbot certonly \
      --standalone \
      --preferred-challenges http \
      -d home.demo.novum.nu \
      --agree-tos \
      -m anton@novum.nu 
```

__

More info: [Innovation app documentation](htts://docs.demo.novum.nu) 
