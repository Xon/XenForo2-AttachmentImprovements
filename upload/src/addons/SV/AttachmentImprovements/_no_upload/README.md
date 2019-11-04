# AttachmentImprovements

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
```php
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

## X-Accel-Redirect with AWS/S3 support

For internal_data hosted on a AWS/S3 bucket (note, resolver must point to a valid DNS resolve);

```
location ~* /internal_data/(.*?)://(.*?)/(.*) { 
    internal; 
    set $xfEtag $upstream_http_etag; 
    set $download_protocol $1; 
    set $download_host $2; 
    set $download_path $3; 
    set $download_url $download_protocol://$download_host/$download_path; 
 
    resolver 127.0.0.1 ipv6=off; 
    proxy_set_header Host $download_host; 
    proxy_set_header Authorization ''; 
    proxy_set_header Cookie ''; 
    proxy_max_temp_file_size 0; 
    proxy_intercept_errors on; 
    error_page 301 302 307 = @handle_redirect; 
 
    proxy_pass $download_url$is_args$args; 
 
    proxy_hide_header Content-Disposition; 
    proxy_hide_header Content-Type; 
    proxy_hide_header Etag; 
    proxy_hide_header x-amz-request-id; 
 
    add_header Etag $xfEtag; 
    add_header X-Frame-Options SAMEORIGIN; 
    add_header X-Content-Type-Options nosniff; 
} 

location @handle_redirect {
   resolver 127.0.0.1 ipv6=off;
   set $saved_redirect_location '$upstream_http_location';
   proxy_pass $saved_redirect_location;
}
```

Additionally, config.php requires a internalDataUrl stanza like any externalDataUrl;

```php
$config['internalDataUrl'] = function($externalPath, $canonical)
{
    return 'internal_data/..../internal_data/' . $externalPath;
};
```

To implement proxy caching see https://www.nginx.com/resources/wiki/start/topics/examples/reverseproxycachingexample/