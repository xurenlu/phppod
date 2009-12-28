#!/bin/bash
INSTALL="no"
FLAGS="-d2  -y"
PKGNAME="phppod"
PKGVERSION="1.0.5"
PKGRELEASE=`date +%Y%m%d`
PKGGROUP="nonfree-renlu"
PKGLICENSE="LGPL"
ARCH="all"
#PAKDIR="./package/"
MAINTAINER="helloasp@hotmail.com"
PROVIDES=""
REQUIRES="echo dpkg"
DOCDIR="./docs/"
#CMD="checkinstall --requires='$REQUIRES' --provides='$PROVIDES' --maintainer='$MAINTAINER' --pakdir='$PAKDIR' --install='$INSTALL' --pkgversion='$PKGVERSION' -A $ARCH --pkgname='$PKGNAME' --docdir='$DOCDIR' --pkggroup='$PKGGROUP' --pkgrelease='$PKGRELEASE' --pkglicense='$PKGLICENSE' $FLAGS "
#CMD="checkinstall  --provides='$PROVIDES' --maintainer='$MAINTAINER' --pakdir='$PAKDIR' --install='$INSTALL' --pkgversion='$PKGVERSION' -A $ARCH --pkgname='$PKGNAME' --docdir='$DOCDIR' --pkggroup='$PKGGROUP' --pkgrelease='$PKGRELEASE' --pkglicense='$PKGLICENSE' $FLAGS $1"
CMD="checkinstall  --provides='$PROVIDES' --maintainer='$MAINTAINER' --pakdir='$PAKDIR' --install='$INSTALL' --pkgversion='$PKGVERSION' -A $ARCH --pkgname='$PKGNAME' --docdir='$DOCDIR' --pkggroup='$PKGGROUP' --pkgrelease='$PKGRELEASE' --pkglicense='$PKGLICENSE' --deldoc=no --deldesc=no --delspec=no --backup=yes $FLAGS $1"
echo $CMD -D
$CMD -D
#$CMD -R
#$CMD -S

