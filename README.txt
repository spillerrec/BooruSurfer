~~~~ BooruSurfer ~~~~
This applications takes advantages of Booru´s API access to emulate the sites in a consistent and space-efficient layout.

~~~~ NOTICE ~~~~
While this application requires a web-server to work, this is not intended for online and/or multi-user scenarios.

~~~~ Requirements ~~~~
-	PHP 5.4
-	curl
-	SSL (for https only sites)
-	mod_rewrite

~~~~ Instalation ~~~~
-	Set this folder to be accessable by the path '/'. Subdomains are fine, but '/boorusurfer/' for example is not.
-	Visit the page '/manage/'
-	Configure each site you want to use, by clicking on each site on the list
	-	Some sites (Danbooru) requires you to log in in order to use it. Please enter your log-in information first.
	-	For the sites which supports fetching of all tags, it greatly improves browsing experience doing so. Notice however that some sites have an enormious amount of tags, so this process can take several minutes to complete.
	-	Finally click the 'Enable' button to activate the site