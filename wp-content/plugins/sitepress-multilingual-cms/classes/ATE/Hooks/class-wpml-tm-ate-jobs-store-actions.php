<?php

/**
 * @todo The hook 'wpml_tm_ate_jobs_store' seems to be never used so this class and its factory may be obsolete
 *
 * @author OnTheGo Systems
 */
class WPML_TM_ATE_Jobs_Store_Actions implements IWPML_Action {
	/**
	 * @var WPML_TM_ATE_Jobs
	 */
	private $ate_jobs;

	/**
	 * WPML_TM_ATE_Jobs_Actions constructor.
	 *
	 * @param WPML_TM_ATE_Jobs $ate_jobs
	 */
	public function __construct( WPML_TM_ATE_Jobs $ate_jobs ) {
		$this->ate_jobs = $ate_jobs;
	}

	public function add_hooks() {
		add_action( 'wpml_tm_ate_jobs_store', array( $this, 'store_action' ), 10, 2 );
	}

	/**
	 * @param int   $wpml_job_id
	 * @param array $ate_job_data
	 *
	 * @return array|null
	 */
	public function store( $wpml_job_id, $ate_job_data ) {
		return $this->ate_jobs->store( $wpml_job_id, $ate_job_data );
	}

	/**
	 * @param int   $wpml_job_id
	 * @param array $ate_job_data
	 *
	 * @return void
	 */
	public function store_action( $wpml_job_id, $ate_job_data ) {
		$this->ate_jobs->store( $wpml_job_id, $ate_job_data );
	}
}
