<?php
require_once '../Board.php';

function DebugRender( Board $board,callback $linker ) : string
{
    $output = '<table>';
    for( $i = 0; $i < 2; $i++ )
    {
        $output .= '<tr>';            
        // pre blank for bottom side
        if( $i === 1 )
        {
            $output .= '<td>-</td>';
        }
        for( $j = 0; $j < 7; $j++ )
        {
            // reverse pot index if top
            if( $i === 0 )
            {
                // modify j because top row runs right to left
                $jm = 6 - $j;
            }
            $output .= '<td><a href="'.$linker( $i,$j ).'">'.$board->GetPot( Pot::FromSideOffset( $i,$jm ) ).'</a></td>';
        }
        // post blank for top side
        if( $i === 0 )
        {
            $output .= '<td>-</td>';
        }
        $output .= '</tr>';
    }
    $output .= '</table>';
    return $output;
}
?>