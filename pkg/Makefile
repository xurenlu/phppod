all:
	echo "nothing to do with 'make all'"
	echo "please execute './package.sh make install  ' to package it"
install:
	mkdir /usr/share/podclient/ -p
	cp ../src/phppod.php /usr/share/podclient/phppod.php
	cp ../src/phpcolor.php /usr/share/podclient/phpcolor.php
	cp ../src/podclient.php /usr/bin/podclient
	cp ../etc/podclientd.conf /etc/podclientd.conf 
	#cp ../src/podclient /etc/init.d/podclient
clean:
	rm *.tgz *.deb description-pak
package:
	sudo sh package.sh
