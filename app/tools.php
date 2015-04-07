<?php

function train_case($text)
{
  if (strpos($text, '_') === false)
    $text = snake_case($text);

  return str_replace(' ', '_', ucwords(str_replace('_', ' ', $text)));
}
