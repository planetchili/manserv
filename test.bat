@echo off
if not !%1 == ! (
	vendor\bin\phpunit.bat --configuration test\phpunit.xml --filter %1
) else (
	vendor\bin\phpunit.bat --configuration test\phpunit.xml
)