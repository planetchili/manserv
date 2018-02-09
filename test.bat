@echo off

setlocal

set arg=%1
set init=%arg:~0,1%
if not !%arg% == ! (
	if %init% == g (
		vendor\bin\phpunit.bat --configuration test\phpunit.xml --group %arg%		
	) else (
		vendor\bin\phpunit.bat --configuration test\phpunit.xml --filter %arg%
	)
) else (
	vendor\bin\phpunit.bat --configuration test\phpunit.xml
)

endlocal