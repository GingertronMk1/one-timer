<?php

namespace App;

enum TimeUnit: string
{
    case MINUTES = 'minutes';
    case HOURS = 'hours';
    case DAYS = 'days';
    case WEEKS = 'weeks';
    case MONTHS = 'months';
    case YEARS = 'years';
}
