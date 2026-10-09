import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/bpmn_node.json';

export default class BpmnNode extends AbstractApiEntity {
  static readonly entityName = 'bpmnNode';

  static retrieveEntitySchema() {
    return schema;
  }
}
