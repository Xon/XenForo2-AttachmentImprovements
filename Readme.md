# XenForo-AttachmentImprovements

A collection of improvements to XF's attachment system.

- SVG support
- nginx's X-Accel-Redirect
- New Permissions for forum/conversations (Respects global attachment size & count limits):
-- Attachment Size (kb).
-- Maximum Attachment Count.

## SVG support

Adds support for SVG files as attachments to be displayable as normal images.

## X-Accel-Redirect

Enables the use of Nginx's X-Accel-Redirect header feature for attachment serving.

This permits XenForo to-do validation and authentication, and offload the actual file serving to Nginx. This feature is not particularly well documented, but some info found here; http://wiki.nginx.org/X-accel

This addon assumes the /internal_data folder exists within XenForo's webroot.

If not, you may need an nginx config construct similar to:
```
    location ^~ /internal_data {
        internal;
        add_header Etag $upstream_http_etag;
        add_header X-Frame-Options SAMEORIGIN;
        add_header X-Content-Type-Options nosniff;
        alias /path/to/internal_data;
    }
```  
And then add in config.php
```
    $config['internalDataUrl'] = '/internal_data';
```  

To ensure you match how XenForo serves files, add the following headers into your site config where appropriate:
```
    add_header Etag $upstream_http_etag;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;
```

ie:
```
location ^~ /internal_data {
    internal;
    add_header Etag $upstream_http_etag;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;
}
```
