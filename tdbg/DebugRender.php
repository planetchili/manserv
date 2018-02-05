<?php
require_once '../Board.php';

function DebugRender( Board $board,Side $ourside,Side $activeSide ) : string
{
    $output = '<table>';
    for( $i = 0; $i < 2; $i++ )
    {
        $side = new Side( $i );
        $output .= '<tr>';            
        // pre blank for bottom side
        if( $i === 1 )
        {
            $output .= '<td>-</td>';
        }
        for( $j = 0; $j < 7; $j++ )
        {
            $jm = $j;
            // reverse pot index if top
            if( $i === 0 )
            {
                // modify j because top row runs right to left
                $jm = 6 - $j;
            }
            
            $pot = Pot::FromSideOffset( new Side( $i ),$jm );
            $beadCount = $board->GetPot( $pot );

            if( $side == $ourside && $ourside == $activeSide && $beadCount > 0 && $jm != 6 )
            {
                $cell = '
                <form method="POST">
                    <input type="hidden" name="pot" value='.Pot::FromSideOffset( new Side( $i ),$jm )->GetIndex().'>
                    <input type="submit" class="pushy" value="'.$beadCount.'">
                </form>';
            }
            else
            {
                $cell = '<p>'.$beadCount.'</p>';
            }

            $output .= '<td>'.$cell.'</td>';
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