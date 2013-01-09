#!/usr/bin/python
# -*- coding: utf-8 -*-

import MySQLdb as mysql
import sys
import subprocess
import os
from datetime import datetime

backupDirectory = "../../backup/"
intervalToKeepData = "2 MONTH"
dbName = "sayso"
dbUser = "api"
dbPass = "pl@n3t@pi"
#mysqlDumpPath = "C:\\path\\to\\mysqldump.exe"
mysqlDumpPath = "/path/to/mysqldump"
maximumRowsToDeletePerQuery = 500000 # if set too low you get too many queries (slow), if set too high, queries time out

backupTables = ["metrics_page_view", "metrics_search", "metrics_social_activity", "metrics_log"]


connection = mysql.connect('localhost', dbUser, dbPass, dbName)

with connection:

	cursor = connection.cursor(mysql.cursors.DictCursor)

	cursor.execute("SELECT UNIX_TIMESTAMP(now() - INTERVAL " + intervalToKeepData + ") AS backup_before")
	row = cursor.fetchone()
	backupBefore = row["backup_before"]

	now_string = datetime.strftime(datetime.now(), "%Y%m%d_%H%M%S")

	if backupBefore:
		backupBefore = str(backupBefore)

		for tableName in backupTables:
			print "Starting backup for " + tableName + "..."
			cursor.execute("SELECT id AS backup_before_id FROM " + tableName + " WHERE created < FROM_UNIXTIME(" + backupBefore + ") ORDER BY id DESC LIMIT 1;")
			row = cursor.fetchone()
			backupBeforeId = row["backup_before_id"]

			if backupBeforeId:
				backupBeforeId = str(backupBeforeId)



				backupFilePath = backupDirectory + "/backup_dump_" + tableName + "_" + now_string + "_" + backupBefore + "_" + backupBeforeId + ".sql"
				backupFileHandle = open(backupFilePath, "w")
				whereClause = "id < " + backupBeforeId

				commandAndArguments = [mysqlDumpPath, "--lock-tables=false", "--quick", "--extended-insert", "--compact", "--complete-insert", "--comments", "--dump-date", "--order-by-primary", "--no-create-info", "--skip-triggers", "--where", whereClause, "--user", dbUser, "--password=" + dbPass, dbName, tableName]
				dumpProcess = subprocess.Popen(commandAndArguments, stdout = backupFileHandle, stderr = subprocess.PIPE)
				dumpProcess.wait()
				backupFileHandle.close()

				try:
					errors = dumpProcess.stderr.read()
				except IOError:
					errors = false
					pass

				if errors:
					print "ERROR!!"
					print errors
					sys.exit(0)

				print "Writing " + tableName + " backup complete!"

				try:
					# Check if backup file exists
					backupFileSize = os.path.getsize(backupFilePath)
					print "Wrote " + str(backupFileSize) + " bytes to " + backupFilePath

					# Delete the data that was just backed up from the database
					print "Deleting backed up data for " + tableName + "..."
					rowsDeleted = 1
					while (rowsDeleted > 0):
						cursor.execute("DELETE FROM " + tableName + " WHERE id < " + backupBeforeId + " LIMIT " + str(maximumRowsToDeletePerQuery) + ";")
						rowsDeleted = cursor.rowcount;
						connection.commit()
					print "Deleting backed up data for " + tableName + " complete!"
				except OSError as e:
					pass

		print "Tables backed up successfully!"
