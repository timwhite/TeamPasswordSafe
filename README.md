TeamPasswordSafe
================

A Team Password Manager/Safe using the Symfony Framework. Written out of a need
for something free, open source, unrestricted, that was also simple for users
so it could be used by volunteers in a not for profit.

It needed to be:
 * Easy for the average user to use
 * Accessible from any device, including Tablets and Mobile Phones
 * Not require a private key to login (See FAQ)
 * Free for as many users as we wanted
 * Provide some restrictions per user (Users shouldn't be able to see all
   passwords in the database, just those they have permission to)

## Requirements

 * Currently needs a patch to the JMSTranslationBundle <https://github.com/schmittjoh/JMSTranslationBundle/pull/285/files>
 * Recommended that you serve it over SSL ([Lets Encrypt provides free
   certificates](https://letsencrypt.org/))


## Installation

Installation instructions coming soon.

## FAQ

### Aren't Private Keys more secure than login passwords?

Sometimes.

For someone to try and brute for a login, private keys are certainly more
secure than using a login password. However, private keys have their issues
too.

 1. Private keys are easily stolen for the average user. Do you encrypt your
    entire computer? DO you share your computer with other users? It's easy for
    someone who gains access to your computer to find the private key, and then
    have access to your password safe. If you use something like Dropbox, you
    may even find that your private key is accessible from other devices as
    well, and then a compromise of your Dropbox account, or another device
    could lead to a loss of key.

 2. Private keys aren't friendly to accessing the password safe from any
    device, anytime. Need to lookup a password on your phone? Now your phone
    needs your private key too. This is when someone is likely to use something
    like Dropbox to ensure they have their private key everywhere.

Assuming you don't write down your master password, and you keep in nice and
long and difficult to guess, your password is more secure than a private key.

### Why not ______ ?

 * Valutier <https://www.vaultier.org/>

   Vaultier uses Private keys (see FAQ) instead of passwords. Vaultier appears
   to have limitations past 5 users

 * RatticDB <http://rattic.org/>

   RatticDB doesn't encrypt your passwords in the Database, instead recommending
   you encrypt your filesystem. This is very risky as most database leaks occur
   from running machines, which an encypted filesystem doesn't protect you from.
 * TeamPass <https://github.com/nilsteampassnet/TeamPass>

   TeamPass uses Private keys (see FAQ) instead of passwords. The interface
   also felt cluttered and not mobile friendly.

## Architecture

TeamPasswordSafe uses the Symfony framework, AdminLTE (Avanzu Admin Theme
Bundle), [Defuse PHP Encryption](https://github.com/defuse/php-encryption), to
enable rapid development, and ensure that I do what I'm good at, writing
coding. I'm not great at encryption, so lets use a library that is. I'm not
great at interfaces, so lets use existing interfaces.

### User
Each user in the system logs in with their login password. This login password
also unlocks their stored private key. The unlocked key is stored in their
session, so we minimise session length.

### Groups
Each group has a group key that is generated at group creation. It is encrypted
with the users public key. Each user that is added to the group has
the group key encrypted with their public key.

### Passwords
Each password in a group is encrypted using the groups key. Access to this key
is through the currently logged in user who can decrypt the group key with
their private key.


## Future Development

 * Allow private keys as login (optional) for those that do know how to
   securely store a private key
 * Work out secure storage of sessions as we store the unlocked private key in
   the session. <https://github.com/timwhite/TeamPasswordSafe/issues/1>
 * Allow admin's to export a backup of all passwords they have access to
 * Write the user management interface for managing all users
(avanzu/admin-theme-bundle
