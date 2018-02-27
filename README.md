It's tools for easily collect web asset to production.
Collector able to compiles "less" and "sass" css files and minify them.
Also It ables to minify js files.
You need is copied the "collector" and "collections.json" files to root directory our application.

```
[
	{
		"name": "REBUS", // name collection
		"base_url": "/", // url
		"root_dir": "/var/www/myapp/" // root directory, fill if you collection locate to another dir
		"compiled_dir": "~public/compiled/", // directory where put collect files
		
		/*
		* use WebCollector\Filter as Filter;
		*
		*	class Less extends Filter {
		*	}
		*/
		"filters": [ //external filters 
			{
				"name": "less",
				"class": "\\Reb\\Less"
			}
		],
		
		
		/*
		* use WebCollector\Transport as Transport;
		*
		*	class Ftp extends Transport {
		*		public function send(){}
		*
    		*	  	public function delete(){}
		*	}
		*/
		"transport": {
			"class": "File",
			"parameters": {
				"name": "Test"
			}
		},
		
		//Bundle css 
		"css": [
			{
				"file": "css/rebus-{d}.css", //name file can use {hash} or {d}{m}{y}{Y}{H}{i}{s}
				"version": "{Y}{m}{d}{H}",
				"minify": true,
				"source": [
					{
						"file": "public/css/main.less",
						"filters": [{
							"name": "less",
							"params": {
								"import_dir": "public/css/import/"
							}
						}]
					},
					{
						"dir": "public/css/scss/",
						"regex": "/^(.*.scss)$/i",
						"filters": ['scss']
					}
				]
			}
		],
		
		//Bundle js 
		"js": [
			{
				"file": "js/rebus.js",
				"minify": true,
				"source": [
					{
						"file": "public/js/main.js",
						"filters": ["less"]
					}
				]
			}
		],
		
		//Copy resources
		"copy": [
			{
				"from": "public/img/",
				"regex": "/^(.*.jpg)$/i",
				"to": "img/"
			}
		]
	}
]
```


Console commands "php ./collector" - compile all collections.

Console commands "php ./collector {collection name}" - compile current collection.

In our application you can use class WebCollector\Web

```
 \WebCollector\Web::getInstance()->CSS(name collection);
 \WebCollector\Web::getInstance()->JS(name collection);
``` 
