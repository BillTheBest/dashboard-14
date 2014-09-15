# Dashboard

Owncloud 7 app that summarizes some global informations on local owncloud instance usage.

These informations are gathered :
* total used space,
* nb of users,
* global folders nb,
* global files nb,
* global shares nb,
* (mean) file size per user,
* (mean) nb of folders per user,
* (mean) nb of files per user,
* (mean) nb of shares per user,
* (mean) size per folder,
* (mean) files per folder,
* (mean) size per file,
* (standard deviation) nb of files per user,
* (standard deviation) nb of folders per user,
* (standard deviation) nb of shares per user

Cron task is used for historization and chart may be displayed for the last week, month, semester or year for one the data in the list above.

## JSON API

JSON API is provided

Real time stats extracting :
`[owncloud]/index.php/apps/dashboard/api/1.0/stats`

History stats :
`[owncloud]/index.php/apps/dashboard/api/1.0/history_stats/[dataType]/[nbDays]`

where
* `[owncloud]` is the web url to your owncloud instance
* `[dataType]` is one of totalUsedSpace, nbUsers,nbFolders, nbFiles,nbShares, sizePerUser, foldersPerUser, filesPerUser, sharesPerUser, sizePerFolder, filesPerFolder, sizePerFile, stdvFilesPerUser, stdvFoldersPerUser, stdvSharesPerUser.
* `[nbDays]` is the number of days from todays you want datas.

## Random test datas

A command line utility exists, allowing to populate this app history table with random datas.

Usage:

```shell
cd [owncloud]/
./occ dashboard:populate
```
where [owncloud] is the install folder of your owncloud instance

Warning : datas are added to the table, so you may want to truncate the `*prefix*_dashboard_history` table before running this command.

## Contributing

This app is developed for ownCoRe CNRS project, an internal deployement of ownCloud at CNRS (French National Center for Scientific Research).

If you want to be informed about owCoRe project at CNRS, please contact david.rousse@dsi.cnrs.fr or gilian.gambini@dsi.cnrs.fr

## License and Author

|                      |                                          |
|:---------------------|:-----------------------------------------|
| **Author:**          | Patrick Paysant (<ppaysant@linagora.com>)
| **Copyright:**       | Copyright (c) 2014 CNRS DSI
| **License:**         | AGPL v3, see the COPYING file.