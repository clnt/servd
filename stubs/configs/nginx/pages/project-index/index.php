<html lang="en">
    <head>
        <title>Project Not Found</title>
    </head>
    <body style="display: flex; justify-content: center; align-items: center;">
        <div style="max-width: 800px;">
            <div style="font-family: Courier, sans-serif; text-align: center">
                <h1 style="margin-bottom: 10px;">Project Not Found</h1>
                <p>
                    The project you are trying to access at <strong><?php echo $_SERVER['HTTP_HOST'] ?></strong> could not be found.
                    <br/><br/>
                    If the directory exists in your working directory, run the scan command to detect it and restart containers.
                </p>
                <h2>Available Projects</h2>
                    {{$availableProjects}}
                <h2>Unknown Projects</h2>
                    {{$unknownProjects}}
            </div>
        </div>
    </body>
</html>
