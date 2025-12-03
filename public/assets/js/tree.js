/**
 * Family Tree D3.js Visualization
 * Heritage Family Tree Application
 */

let svg, g, tree, root;
let currentLayout = 'vertical';
let i = 0;
const duration = 750;
const nodeRadius = 8;

// Layout dimensions
const margin = { top: 40, right: 120, bottom: 40, left: 120 };
let width = 960 - margin.right - margin.left;
let height = 600 - margin.top - margin.bottom;

/**
 * Initialize and render tree
 */
function renderTree(data) {
    // Clear existing tree
    d3.select('#tree-container').html('');
    
    // Update dimensions based on container
    const container = document.getElementById('tree-container');
    width = container.clientWidth - margin.left - margin.right;
    height = 600 - margin.top - margin.bottom;
    
    // Create SVG
    svg = d3.select('#tree-container')
        .append('svg')
        .attr('width', width + margin.right + margin.left)
        .attr('height', height + margin.top + margin.bottom)
        .call(d3.zoom().on('zoom', (event) => {
            g.attr('transform', event.transform);
        }));
    
    g = svg.append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);
    
    // Create tree layout
    tree = d3.tree().size([height, width]);
    
    // Convert data to hierarchy
    root = d3.hierarchy(data);
    root.x0 = height / 2;
    root.y0 = 0;
    
    // Collapse all nodes initially except root
    if (root.children) {
        root.children.forEach(collapse);
    }
    
    update(root);
}

/**
 * Update tree visualization
 */
function update(source) {
    // Compute the new tree layout
    const treeData = tree(root);
    const nodes = treeData.descendants();
    const links = treeData.descendants().slice(1);
    
    // Normalize for fixed-depth
    nodes.forEach(d => {
        d.y = d.depth * 180;
    });
    
    // Update nodes
    const node = g.selectAll('g.node')
        .data(nodes, d => d.id || (d.id = ++i));
    
    // Enter new nodes
    const nodeEnter = node.enter().append('g')
        .attr('class', 'node tree-node')
        .attr('transform', d => `translate(${source.y0},${source.x0})`)
        .on('click', (event, d) => {
            if (d.children || d._children) {
                toggleNode(event, d);
            } else {
                showPersonDetails(d.data);
            }
        });
    
    // Add circles for nodes
    nodeEnter.append('circle')
        .attr('r', 1e-6)
        .style('fill', d => d._children ? 'lightsteelblue' : '#fff')
        .attr('class', d => {
            if (d.data.gender === 'male') return 'male';
            if (d.data.gender === 'female') return 'female';
            return '';
        });
    
    // Add labels
    nodeEnter.append('text')
        .attr('dy', '.35em')
        .attr('x', d => d.children || d._children ? -13 : 13)
        .attr('text-anchor', d => d.children || d._children ? 'end' : 'start')
        .text(d => d.data.name)
        .style('font-size', '12px')
        .style('fill-opacity', 1e-6);
    
    // Add birth/death dates
    nodeEnter.append('text')
        .attr('dy', '1.5em')
        .attr('x', d => d.children || d._children ? -13 : 13)
        .attr('text-anchor', d => d.children || d._children ? 'end' : 'start')
        .text(d => {
            if (d.data.birthdate) {
                const birth = new Date(d.data.birthdate).getFullYear();
                const death = d.data.deathdate ? new Date(d.data.deathdate).getFullYear() : '';
                return death ? `(${birth} - ${death})` : `(b. ${birth})`;
            }
            return '';
        })
        .style('font-size', '10px')
        .style('fill', '#666')
        .style('fill-opacity', 1e-6);
    
    // Merge enter and update selections
    const nodeUpdate = nodeEnter.merge(node);
    
    // Transition to proper position
    nodeUpdate.transition()
        .duration(duration)
        .attr('transform', d => `translate(${d.y},${d.x})`);
    
    // Update circle style
    nodeUpdate.select('circle')
        .attr('r', nodeRadius)
        .style('fill', d => d._children ? 'lightsteelblue' : '#fff')
        .attr('cursor', 'pointer');
    
    // Update text opacity
    nodeUpdate.selectAll('text')
        .style('fill-opacity', 1);
    
    // Remove exiting nodes
    const nodeExit = node.exit().transition()
        .duration(duration)
        .attr('transform', d => `translate(${source.y},${source.x})`)
        .remove();
    
    nodeExit.select('circle')
        .attr('r', 1e-6);
    
    nodeExit.select('text')
        .style('fill-opacity', 1e-6);
    
    // Update links
    const link = g.selectAll('path.link')
        .data(links, d => d.id);
    
    // Enter new links
    const linkEnter = link.enter().insert('path', 'g')
        .attr('class', 'link tree-link')
        .attr('d', d => {
            const o = { x: source.x0, y: source.y0 };
            return diagonal(o, o);
        });
    
    // Merge and transition links
    linkEnter.merge(link).transition()
        .duration(duration)
        .attr('d', d => diagonal(d, d.parent));
    
    // Remove exiting links
    link.exit().transition()
        .duration(duration)
        .attr('d', d => {
            const o = { x: source.x, y: source.y };
            return diagonal(o, o);
        })
        .remove();
    
    // Store old positions
    nodes.forEach(d => {
        d.x0 = d.x;
        d.y0 = d.y;
    });
}

/**
 * Create diagonal path
 */
function diagonal(s, d) {
    return `M ${s.y} ${s.x}
            C ${(s.y + d.y) / 2} ${s.x},
              ${(s.y + d.y) / 2} ${d.x},
              ${d.y} ${d.x}`;
}

/**
 * Toggle node children
 */
function toggleNode(event, d) {
    if (d.children) {
        d._children = d.children;
        d.children = null;
    } else {
        d.children = d._children;
        d._children = null;
    }
    update(d);
}

/**
 * Collapse node
 */
function collapse(d) {
    if (d.children) {
        d._children = d.children;
        d._children.forEach(collapse);
        d.children = null;
    }
}

/**
 * Expand node
 */
function expand(d) {
    if (d._children) {
        d.children = d._children;
        d.children.forEach(expand);
        d._children = null;
    }
}

/**
 * Reset zoom
 */
function resetZoom() {
    svg.transition()
        .duration(750)
        .call(d3.zoom().transform, d3.zoomIdentity);
}

/**
 * Expand all nodes
 */
function expandAll() {
    root.children.forEach(expand);
    update(root);
}

/**
 * Collapse all nodes
 */
function collapseAll() {
    root.children.forEach(collapse);
    update(root);
}

/**
 * Set layout orientation
 */
function setLayout(layout) {
    currentLayout = layout;
    
    // Update button states
    document.getElementById('btnVertical').classList.toggle('active', layout === 'vertical');
    document.getElementById('btnHorizontal').classList.toggle('active', layout === 'horizontal');
    
    if (layout === 'horizontal') {
        tree = d3.tree().size([width, height]);
    } else {
        tree = d3.tree().size([height, width]);
    }
    
    update(root);
}

/**
 * Show person details in modal
 */
async function showPersonDetails(person) {
    const modal = new bootstrap.Modal(document.getElementById('personModal'));
    const title = document.getElementById('personModalTitle');
    const body = document.getElementById('personModalBody');
    const link = document.getElementById('personModalLink');
    
    title.textContent = person.name;
    body.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    modal.show();
    
    try {
        const result = await apiCall(`/persons/${person.id}`);
        const p = result.person;
        
        let html = '<dl class="row">';
        html += `<dt class="col-sm-4">Full Name:</dt><dd class="col-sm-8">${p.fullname}</dd>`;
        
        if (p.gender) {
            html += `<dt class="col-sm-4">Gender:</dt><dd class="col-sm-8">${p.gender.charAt(0).toUpperCase() + p.gender.slice(1)}</dd>`;
        }
        
        if (p.birthdate) {
            html += `<dt class="col-sm-4">Birth Date:</dt><dd class="col-sm-8">${new Date(p.birthdate).toLocaleDateString()}</dd>`;
        }
        
        if (p.birthplace) {
            html += `<dt class="col-sm-4">Birth Place:</dt><dd class="col-sm-8">${p.birthplace}</dd>`;
        }
        
        if (!p.is_alive && p.deathdate) {
            html += `<dt class="col-sm-4">Death Date:</dt><dd class="col-sm-8">${new Date(p.deathdate).toLocaleDateString()}</dd>`;
        }
        
        html += `<dt class="col-sm-4">Status:</dt><dd class="col-sm-8">`;
        html += p.is_alive ? '<span class="badge bg-success">Alive</span>' : '<span class="badge bg-secondary">Deceased</span>';
        html += '</dd>';
        
        if (result.parents && result.parents.length > 0) {
            html += `<dt class="col-sm-4">Parents:</dt><dd class="col-sm-8">`;
            result.parents.forEach(parent => {
                html += `${parent.fullname}<br>`;
            });
            html += '</dd>';
        }
        
        if (result.children && result.children.length > 0) {
            html += `<dt class="col-sm-4">Children:</dt><dd class="col-sm-8">${result.children.length}</dd>`;
        }
        
        if (result.spouses && result.spouses.length > 0) {
            html += `<dt class="col-sm-4">Spouse(s):</dt><dd class="col-sm-8">`;
            result.spouses.forEach(spouse => {
                html += `${spouse.fullname}<br>`;
            });
            html += '</dd>';
        }
        
        if (p.notes) {
            html += `<dt class="col-sm-4">Notes:</dt><dd class="col-sm-8">${p.notes}</dd>`;
        }
        
        html += '</dl>';
        
        body.innerHTML = html;
        link.href = `persons/show.php?id=${person.id}`;
    } catch (error) {
        body.innerHTML = '<div class="alert alert-danger">Failed to load person details</div>';
    }
}

/**
 * Handle window resize
 */
window.addEventListener('resize', () => {
    if (root) {
        const container = document.getElementById('tree-container');
        width = container.clientWidth - margin.left - margin.right;
        
        svg.attr('width', width + margin.right + margin.left);
        
        if (currentLayout === 'horizontal') {
            tree.size([width, height]);
        } else {
            tree.size([height, width]);
        }
        
        update(root);
    }
});