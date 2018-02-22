It's tools for easily collect web asset to production.
Collector able to compiles "less" and "sass" css files and minify them.
Also It ables to minify js files.
You need is copied the "collector" and "collections.json" files to root directory our application.

For examples:
if you need to send files on extarnal server after collected you can use it "transport" parameter.
Your class has to inherit class WebCollector/Transport and implements two methods "send" and "delete".

```
[
	{
		"name": "REBUS",
		"base_url": "/",
		"compiled_dir": "public/compiled/",
		"transport": {
			"class": "YourClass",
			"parameters": {
				"name": "Test"
			}
		},
		"css": [
			{
				"file": "public/css/main.css",
				"minify": true
			},
			{
				"file": "public/css/input.less",
				"import_dir": "public/css/",
				"minify": true
			}
		],
		"js": []
	}
]
```

Or simple example

```
[
	{
		"name": "REBUS",
		"base_url": "/",
		"compiled_dir": "public/compiled/",
		"css": [
			{
				"file": "public/css/main.css",
				"minify": true
			},
			{
				"file": "public/css/input.less",
				"import_dir": "public/css/",
				"minify": true
			}
		],
		"js": []
	}
]
```

Console commands "php ./collector" - compile all collections.
Console commands "php ./collector {collection name}" - compile current collection.

In our application you can use class WebCollector\Web

 \WebCollector\Web::getInstance()->CSS(name collection);
 \WebCollector\Web::getInstance()->JS(name collection);
