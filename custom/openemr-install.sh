#!/bin/bash
#
# Create a clean install of ${DBNAME} from a git branch
# assumes dbname = user = password
USRGRP=$1
GITDIR=$2 # source
SITEDIR=$3 # destination

#echo Enter DB Name:
#read DBNAME

echo Update as ${USRGRP} from "${GITDIR}" to "${SITEDIR}"
echo Continue?
read X

echo Starting...
sudo rm -fr ${SITEDIR}/*

#echo Drop DB ...
#mysqladmin -f -h localhost -u root -p drop ${DBNAME}
#mysql -f -u root -p -h localhost -e "DELETE FROM mysql.user WHERE User = '${DBNAME}';FLUSH PRIVILEGES;"

echo Sync files ...
sudo rsync -i --recursive --exclude .git ${GITDIR}/* ${SITEDIR}/

echo Permissions
sudo chmod -v 666 ${SITEDIR}/openemr/interface/modules/zend_modules/config/application.config.php
sudo chmod -v 666 ${SITEDIR}/openemr/sites/default/sqlconf.php
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/sites/default/documents
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/sites/default/edi
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/sites/default/era
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/library/freeb
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/sites/default/letter_templates
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/compiled
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/interface/main/calendar/modules/PostCalendar/pntemplates/cache
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/gacl/admin/templates_c
sudo chown -Rv ${USRGRP} ${SITEDIR}/openemr/interface/forum/templates_c

#echo "Edit Globals.php"
#vi openemr/interface/globals.php
