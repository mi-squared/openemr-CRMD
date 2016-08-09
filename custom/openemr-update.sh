#!/bin/bash
#
# Copy the new openemr version to the web directory 
# does not touch sites, globals.php or database

USRGRP=$1
GITDIR=$2 # source
SITEDIR=$3 #destination

echo Update "${GITDIR}" to "${SITEDIR}"
echo Continue?
read X

echo Starting...

sudo rm -rf ${SITEDIR}/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/compiled/*
sudo rm -rf ${SITEDIR}/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/cache/*
sudo rm -rf ${SITEDIR}/openemr/gacl/admin/templates_c/*

sudo rsync -i --recursive --delete --exclude .git --exclude sites ${GITDIR}/* ${SITEDIR}/

# modify permissions
sudo chmod 666 ${SITEDIR}/openemr/sites/default/sqlconf.php
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/library/freeb
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/compiled
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/cache
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/gacl/admin/templates_c

