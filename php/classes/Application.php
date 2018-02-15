<?php

/**
 *   Application class
 *
 */
class Application {

	/**
	 * 
	 * Gets the application version and build type
	 *
	 * @return array $build : the application build
	 */
	public function getApplicationBuild () {
		$build = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					bt.Name 
				FROM 
					BuildType bt
				LIMIT 1
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$buildName = $data[0];

			// $versionFile = fopen("../../VERSION", "r") 
			// 	or die("Unable to open VERSION file!");

			// $version = fgets($versionFile);
			// fclose($versionFile);

			$version = shell_exec('git describe');
			$branch = shell_exec('git rev-parse --abbrev-ref HEAD');

			$build = array(
				'version'		=> $version,
				'environment'	=> $buildName,
				'branch'		=> $branch
			);

			return $build;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $build;
		}

	}
}