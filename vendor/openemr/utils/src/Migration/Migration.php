<?php
namespace Migration;

abstract class Migration
{
    /**
     * @return string containing migration directoves
     */
    public abstract function up();
    
    /**
     * @return string containing migration directoves
     */
    public abstract function down();
}
