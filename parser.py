from xml.dom.minidom import parse
import sys
import codecs

sys.stdout = codecs.getwriter('utf8')(sys.stdout)

globalid = 1

def genId():
    global globalid
    ret = globalid
    globalid += 1
    return ret

def handleSheet(node):
    if node.firstChild.nodeName == 'topic':
        handleTopic(node.firstChild, 0)

def handleTopic(topic, parentId):
    nodeid = genId()
    nodetype = 0
    nodelabel = ''
    nodetitle = ''
    nodenote = ''
    nodetext = ''

    children = topic.childNodes
    for node in children:
        if node.nodeName == 'marker-refs':
            marker = node.firstChild.attributes['marker-id'].value
            if marker == 'priority-1':
                nodetype = 1
            elif marker == 'priority-2':
                nodetype = 2
            elif marker == 'priority-3':
                nodetype = 3
        elif node.nodeName == 'labels':
            nodelabel = node.firstChild.firstChild.data
        elif node.nodeName == 'children':
            for child in node.childNodes:
                if child.nodeName == 'topics':
                    for childtopic in child.childNodes:
                        handleTopic(childtopic, nodeid)
        elif node.nodeName == 'title':
            nodetitle = node.firstChild.data
        elif node.nodeName == 'notes':
            nodenote = node.getElementsByTagName('plain')[0].firstChild.data

    nodetext = nodetitle + "\n" + nodenote
    #nodetext = nodetext.replace("\n", "\\n")
    output = """insert into nodes (nodeindex, nodetype, parentindex, label, message) values(%d,%d,%d,'%s','%s');""" % (nodeid, nodetype, parentId, nodelabel, nodetext)
    output.encode('UTF-8')
    sys.stdout.write(output)
    sys.stdout.write(u"\r\n")


dom = parse('content.xml')

handleSheet(dom.getElementsByTagName('sheet')[0])

