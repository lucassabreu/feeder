build:
	@echo "Compiling...";
	@lessc less/bootstrap.less > php/css/bootstrap.css
	@lessc less/bootstrap.less --compress > php/css/bootstrap.min.css
	
clear:
	echo "Removing...";
	@rm php/css/bootstrap.css
	@rm php/css/bootstrap.min.css