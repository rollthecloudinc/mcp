<?php
interface MCPService {
    /*
    * Check current users permissions to access the service.
    */
    public function checkPerms();
    
    /*
    * Execute the service 
    */
    public function exec();
}
