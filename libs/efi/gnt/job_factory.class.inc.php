<?php
namespace efi\gnt;

abstract class job_factory {
    public abstract function new_gnn($db, $id);
    public abstract function new_gnn_bigscape_job($db, $id);
    public abstract function new_uploaded_bigscape_job($db, $id);
    public abstract function new_diagram_data_file($db, $id);
    public abstract function new_direct_gnd_file($file);
}


