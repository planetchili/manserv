@echo off

setlocal
set params=%*
if not "%params%" == "" (
	if "%params:~0,1%" == "g" (
		vendor\bin\phpunit.bat --configuration test\phpunit.xml --group %params%
	) else (
		vendor\bin\phpunit.bat --configuration test\phpunit.xml --filter %params%
	)
) else (
	vendor\bin\phpunit.bat --configuration test\phpunit.xml
)
endlocal