<?php
function displayNestableMenu($parent_id, $level, $Is_Edit, $Is_Delete = 0)
{
    $result = DB::select("
        SELECT m.id, m.menu_name, m.menu_url, m.status, tbl.count
        FROM menus m
        LEFT OUTER JOIN (
            SELECT parent_id, COUNT(*) AS count
            FROM menus
            GROUP BY parent_id
        ) tbl ON m.id = tbl.parent_id
        WHERE m.parent_id = " . $parent_id . "
        ORDER BY menu_position ASC
    ");

    $result = array_map(function ($value) {
        return (array)$value;
    }, $result);

    $render_html = '';
    if ($result) {
        $render_html .= '<ol class="dd-list">';
        foreach ($result as $row) {
            $menu_name = htmlspecialchars($row['menu_name']);
            $has_children = $row['count'] > 0;

            $render_html .= '<li class="dd-item dd3-item" data-id="' . $row['id'] . '">';
            
            // Drag handle with Velzon icon
            $render_html .= '<div class="dd-handle dd3-handle">';
            $render_html .= '<i class="ri-drag-move-2-line align-middle"></i>'; // Velzon drag icon
            $render_html .= '</div>';
            
            // Menu content
            $render_html .= '<div class="dd3-content d-flex justify-content-between align-items-center">';
            $render_html .= '<span class="text-truncate">' . $menu_name . '</span>';
            $render_html .= '<div class="btn-group btn-group-sm">';
            
            if ($Is_Edit) {
                $render_html .= '<button type="button" class="btn btn-soft-primary btn-sm" onclick="get_form(' . $row['id'] . ')">';
                $render_html .= '<i class="ri-pencil-line align-bottom"></i> Edit';
                $render_html .= '</button>';
            }
            
            if ($Is_Delete && empty(trim($row['menu_url']))) {
                $render_html .= '<button type="button" class="btn btn-soft-danger btn-sm" onclick="delete_menu(this, ' . $row['id'] . ')">';
                $render_html .= '<i class="ri-delete-bin-line align-bottom"></i> Delete';
                $render_html .= '</button>';
            }
            
            $render_html .= '</div></div>';
            
            // Recursive call for children
            if ($has_children) {
                $render_html .= displayNestableMenu($row['id'], $level + 1, $Is_Edit, $Is_Delete);
            }
            
            $render_html .= '</li>';
        }
        $render_html .= '</ol>';
    }

    return $render_html;
}



function getMenuList()
{
    // Fetch all menus ordered by position
    $menus = \App\Models\Menu::whereNull('deleted_at')->orderBy('menu_position')->get();

    $menuTree = [];
    $menuMap = [];

    foreach ($menus as $menu) {
        $menuMap[$menu->id] = $menu;
    }

    foreach ($menuMap as $menu) {
        if ($menu->parent_id == 0) {
            $menuTree[] = $menu;
        } else {
            if (isset($menuMap[$menu->parent_id])) {
                $parent = $menuMap[$menu->parent_id];
                $parent->children = array_merge($parent->children, [$menu]);
            }
        }
    }

    return $menuTree;
}

 function addChildToParent(&$children, $menu)
{
    foreach ($children as $child) {
        if ($child->id == $menu->parent_id) {
            $child->children[] = $menu;
            return true;
        } elseif (isset($child->children)) {
            addChildToParent($child->children, $menu);
        }
    }
    return false;
}


function generateIconArray()
{
    $iconsJson = '{"Buildings":{"home":["house","home"],"home-2":["house","home"],"home-3":["house","home"],"home-4":["house","home"],"home-5":["house","home"],"home-6":["house","home"],"home-7":["house","home"],"home-8":["house","home"],"home-gear":["house","factory"],"home-wifi":["smart home","furniture"],"home-smile":["house","smart home","smile"],"home-smile-2":["house","smart home","smile"],"home-heart":["house","heart","home","orphanage"],"building":["city","office","enterprise"],"building-2":["city","office","construction","enterprise"],"building-3":["factory","plant","enterprise"],"building-4":["city","office","enterprise"],"hotel":["building","hotel","office","enterprise","tavern"],"community":["building","hotel","community"],"government":["building","government"],"bank":["bank","finance","savings","banking"],"store":["shop","mall","supermarket"],"store-2":["shop","mall","supermarket"],"store-3":["shop","mall","supermarket"],"hospital":["medical","health"],"ancient-gate":["historical","scenic","trip","travel"],"ancient-pavilion":["historical","scenic","trip","travel"]},"Business":{"mail":["envelope","email","inbox"],"mail-open":["envelope","email","inbox"],"mail-send":["envelope","email","inbox"],"mail-unread":["envelope","email","inbox","unread"],"mail-add":["envelope","email","inbox","add"],"mail-check":["envelope","email","inbox","read"],"mail-close":["envelope","email","inbox","failed"],"mail-download":["envelope","email","inbox","download"],"mail-forbid":["envelope","email","inbox","privacy"],"mail-lock":["envelope","email","inbox","lock"],"mail-settings":["envelope","email","inbox","settings"],"mail-star":["envelope","email","inbox","favorite"],"mail-volume":["envelope","email","inbox","promotional email","subscription"],"inbox":["inbox"],"inbox-archive":["inbox","archive"],"inbox-unarchive":["unzip","unpack","extract","inbox","unarchive"],"cloud":["weather","cloud"],"cloud-off":["offline-mode","connection-fail","cloud","offline"],"attachment":["annex","paperclip","attachment"],"profile":["id","profile"],"archive":["box","archive"],"archive-drawer":["night table","archive","drawer"],"at":["@","mention"],"award":["medal","achievement","badge"],"medal":["award","achievement","badge"],"medal-2":["award","achievement","badge"],"bar-chart":["statistics","rhythm"],"bar-chart-horizontal":["statistics","rhythm"],"bar-chart-2":["statistics","rhythm"],"bar-chart-box":["statistics","rhythm"],"bar-chart-grouped":["statistics","rhythm"],"bubble-chart":["data","analysis"],"pie-chart":["data","analysis"],"pie-chart-2":["data","analysis"],"pie-chart-box":["data","analysis"],"donut-chart":["data","analysis"],"line-chart":["data","analysis"],"bookmark":["tag","bookmark"],"bookmark-2":["tag","bookmark"],"bookmark-3":["tag","bookmark"],"briefcase":["bag","baggage","briefcase"],"briefcase-2":["bag","baggage","briefcase"],"briefcase-3":["bag","baggage","briefcase"],"briefcase-4":["bag","baggage","briefcase"],"briefcase-5":["bag","baggage","briefcase"],"calculator":["calculator"],"calendar":["date","plan","schedule","agenda","calendar"],"calendar-2":["date","plan","schedule","agenda","calendar"],"calendar-event":["date","plan","schedule","agenda","calendar"],"calendar-todo":["date","plan","schedule","agenda","calendar"],"calendar-check":["date","plan","schedule","agenda","check-in","calendar"],"customer-service":["headset","customer service"],"customer-service-2":["headset","customer service"],"flag":["banner","pin","flag"],"flag-2":["banner","pin","flag"],"global":["earth","union","world","language"],"honour":["honor","glory"],"links":["connection","address","links"],"printer":["printer"],"printer-cloud":["printer","cloud"],"record-mail":["voice mail","tape"],"reply":["forward","reply"],"send-plane":["send","paper plane"],"send-plane-2":["send","paper plane"],"projector":["projection","meeting"],"projector-2":["projection","meeting"],"slideshow":["presentation","meeting","slideshow"],"slideshow-2":["presentation","meeting","slideshow"],"slideshow-3":["presentation","meeting","slideshow"],"slideshow-4":["presentation","meeting","slideshow"],"window":["browser","program","web","window"],"window-2":["browser","program","web","window"],"stack":["layers","stack"],"service":["heart","handshake","cooperation"],"registered":["registered","trademark"],"trademark":["registered","trademark"],"advertisement":["ad","advertisement"],"copyright":["copyright"],"creative-commons":["creative commons"],"creative-commons-by":["attribution","copyright"],"creative-commons-nc":["noncommercial","copyright"],"creative-commons-nd":["no derivative works","copyright"],"creative-commons-sa":["share alike","copyright"],"creative-commons-zero":["cc0","copyright"]},"Communication":{"chat-1":["message","reply","comment"],"chat-2":["message","reply","comment"],"chat-3":["message","reply","comment"],"chat-4":["message","reply","comment"],"message":["chat","reply","comment"],"message-2":["chat","reply","comment"],"message-3":["chat","reply","comment"],"chat-check":["message","reply","comment"],"chat-delete":["message","comment","delete"],"chat-forward":["message","comment","forward"],"chat-upload":["message","comment","upload"],"chat-download":["message","download"],"chat-new":["message","reply","comment"],"chat-settings":["message","comment","settings"],"chat-smile":["message","reply","comment"],"chat-smile-2":["message","reply","comment"],"chat-smile-3":["message","reply","comment"],"chat-heart":["message","reply","comment","heart"],"chat-off":["message","reply","comment","slash"],"feedback":["message","comment","feedback"],"discuss":["message","reply","comment","discuss"],"question-answer":["message","reply","comment","question"],"questionnaire":["message","comment","help"],"video-chat":["message","comment","video"],"chat-voice":["message","comment","voice"],"chat-quote":["message","reply","comment","quote"],"chat-follow-up":["message","reply","comment"],"chat-poll":["message","vote","questionnaire"],"chat-history":["message","history"],"chat-private":["message","private"]},"Design":{"pencil":["edit","pencil"],"edit":["pencil","edit"],"edit-2":["pencil","edit"],"ball-pen":["pen"],"quill-pen":["quill pen"],"mark-pen":["mark pen"],"markup":["markup"],"pen-nib":["pen nib"],"edit-box":["edit"],"edit-circle":["edit"],"sip":["sip"],"brush":["brush"],"brush-2":["brush"],"brush-3":["brush"],"brush-4":["brush"],"paint-brush":["paint brush"],"contrast":["brightness","contrast"],"contrast-2":["moon","dark","contrast"],"drop":["water","drop"],"blur-off":["water","drop","slash"],"contrast-drop":["water","contrast"],"contrast-drop-2":["water","contrast"],"compasses":["compasses"],"compasses-2":["compasses"],"scissors":["scissors"],"scissors-cut":["scissors","cut"],"scissors-2":["scissors","cut"],"slice":["knife","slice"],"eraser":["remove formatting","eraser"],"ruler":["ruler"],"ruler-2":["ruler"],"pencil-ruler":["design","pencil","ruler"],"pencil-ruler-2":["design","pencil","ruler"],"t-box":["text","font"],"input-method":["input method","text"],"artboard":["grid","crop","artboard"],"artboard-2":["artboard"],"crop":["crop"],"crop-2":["crop"],"screenshot":["capture","screenshot"],"screenshot-2":["capture","screenshot"],"drag-move":["arrow","drag","move"],"drag-move-2":["arrow","drag","move"],"focus":["aim","target","focus"],"focus-2":["aim","target","focus"],"focus-3":["aim","target","focus"],"paint":["paint"],"palette":["palette"],"pantone":["pantone"],"shape":["border","shape"],"shape-2":["border","shape"],"magic":["fantasy","magic stick","beautify"],"anticlockwise":["rotate","left","anticlockwise"],"anticlockwise-2":["rotate","left","anticlockwise"],"clockwise":["rotate","right","clockwise"],"clockwise-2":["rotate","right","clockwise"],"hammer":["hammer"],"tools":["settings","tools"],"drag-drop":["drag and drop","mouse"],"table":["table"],"table-alt":["table"],"layout":["layout"],"layout-2":["collage","layout"],"layout-3":["collage","layout"],"layout-4":["collage","layout"],"layout-5":["collage","layout"],"layout-6":["collage","layout"],"layout-column":["layout"],"layout-row":["layout"],"layout-top":["layout"],"layout-right":["layout"],"layout-bottom":["layout"],"layout-left":["layout"],"layout-top-2":["layout"],"layout-right-2":["layout"],"layout-bottom-2":["layout"],"layout-left-2":["layout"],"layout-grid":["layout"],"layout-masonry":["layout"]}}';
    $icons = json_decode($iconsJson, true);
    $iconArray = [];
    foreach ($icons as $category => $iconList) {
        foreach ($iconList as $icon => $attributes) {
            $iconArray[] = $icon;
        }
    }

    return $iconArray;
}
