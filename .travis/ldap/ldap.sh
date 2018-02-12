#!/usr/bin/env sh
set -e
set -x

BASE_PATH="$( cd `dirname $0`/common && pwd )"
SEED_PATH="$( cd `dirname $0`/seed   && pwd )"

DEBIAN_FRONTEND=noninteractive sudo -E apt-get install -y --force-yes slapd time ldap-utils

sudo /etc/init.d/slapd stop

TMPDIR=$(mktemp -d)
cd $TMPDIR

# Delete data and reconfigure.
sudo cp -v /var/lib/ldap/DB_CONFIG ./DB_CONFIG
sudo rm -rf /etc/ldap/slapd.d/*
sudo rm -rf /var/lib/ldap/*
sudo cp -v ./DB_CONFIG /var/lib/ldap/DB_CONFIG
sudo slapadd -F /etc/ldap/slapd.d -b "cn=config" -l $BASE_PATH/slapd.conf.ldif
# Load memberof and ref-int overlays and configure them.
sudo slapadd -F /etc/ldap/slapd.d -b "cn=config" -l $BASE_PATH/memberof.ldif

# Add base domain.
sudo slapadd -F /etc/ldap/slapd.d <<EOM
dn: dc=enabel,dc=be
objectClass: top
objectClass: domain
dc: enabel
EOM

sudo chown -R openldap.openldap /etc/ldap/slapd.d
sudo chown -R openldap.openldap /var/lib/ldap

sudo /etc/init.d/slapd start

# Import seed data.
# NOTE: use ldapadd in order for memberOf and refint to apply, instead of:
# /vagrant/services/ldap/openldap/seed.rb | sudo slapadd -F /etc/ldap/slapd.d
cat $SEED_PATH/seed.ldif |
  /usr/bin/time sudo ldapadd -x -D "cn=admin,dc=enabel,dc=be" -w passwOrD \
               -h localhost -p 389

sudo rm -rf $TMPDIR