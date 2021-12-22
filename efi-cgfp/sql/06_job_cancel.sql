
DROP TABLE IF EXISTS `job_cancel`;
CREATE TABLE `job_cancel` (
    `job_process_num` INTEGER(11) DEFAULT NULL,
    `cancel_status` VARCHAR(10) DEFAULT NULL,
    PRIMARY KEY (`job_process_num`)
);

